<?php
session_start();
include 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['token']) && !isset($_SESSION['_userid'])) {
    header('Location: index.php');
    exit();
}

// Display SweetAlert notifications
if (isset($_GET['i']) && !empty($_GET['i'])) {
    $message = json_encode($_GET['i']);
    $icon = "warning";
    $title = "Oops!";
    displaySweetAlert($icon, $title, $message);
} elseif (isset($_GET['s']) && !empty($_GET['s'])) {
    $message = json_encode($_GET['s']);
    $icon = "success";
    $title = "Success!";
    displaySweetAlert($icon, $title, $message);
} elseif (isset($_GET['e']) && !empty($_GET['e'])) {
    $message = json_encode($_GET['e']);
    $icon = "error";
    $title = "Error!";
    displaySweetAlert($icon, $title, $message);
}

function displaySweetAlert($icon, $title, $message) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>';
    echo '    document.addEventListener("DOMContentLoaded", function() {';
    echo '        Swal.fire({';
    echo '            icon: "'.$icon.'",';
    echo '            title: "'.$title.'",';
    echo '            text: '.$message.',';
    echo '            confirmButtonColor: "#3085d6"';
    echo '        });';
    echo '    });';
    echo '</script>';
}

// Handle profile guard activation/deactivation
if (isset($_POST['activate'])) {
    $api_url = 'https://graph.facebook.com/graphql?variables={"0":{"is_shielded":true,"session_id":"9b78191c-84fd-4ab6-b0aa-19b39f04a6bc","client_mutation_id":"b0316dd6-3fd6-4beb-aed4-bb29c5dc64b0"}}&method=post&doc_id=1477043292367183&query_name=IsShieldedSetMutation&strip_defaults=false&strip_nulls=false&locale=en_US&client_country_code=US&fb_api_req_friendly_name=IsShieldedSetMutation&fb_api_caller_class=IsShieldedSetMutation&access_token='.urlencode($_SESSION['token']);
    
    $result = callFacebookAPI($api_url);
    
    if ($result['extensions']['is_final'] === true) {
        header("Location: profile.php?s=Profile Guard successfully activated!");
    } else {
        header("Location: profile.php?e=Sorry, an error occurred while processing your request. Please try again later.");
    }
    exit();
}

if (isset($_POST['deactivate'])) {
    $api_url = 'https://graph.facebook.com/graphql?variables={"0":{"is_shielded":false,"session_id":"9b78191c-84fd-4ab6-b0aa-19b39f04a6bc","client_mutation_id":"b0316dd6-3fd6-4beb-aed4-bb29c5dc64b0"}}&method=post&doc_id=1477043292367183&query_name=IsShieldedSetMutation&strip_defaults=false&strip_nulls=false&locale=en_US&client_country_code=US&fb_api_req_friendly_name=IsShieldedSetMutation&fb_api_caller_class=IsShieldedSetMutation&access_token='.urlencode($_SESSION['token']);
    
    $result = callFacebookAPI($api_url);
    
    if ($result['extensions']['is_final'] === true) {
        header("Location: profile.php?s=Profile Guard successfully deactivated!");
    } else {
        header("Location: profile.php?e=Sorry, an error occurred while processing your request. Please try again later.");
    }
    exit();
}

function callFacebookAPI($url) {
    global $useragent;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

// Display profile page if token is valid
if ($_SESSION['token']) {
    $access_token = $_SESSION['token'];
    $me = me($access_token);
    if (!$me['name']) {
        invalidToken();
    } else {
        displayProfilePage($me);
    }
}

function displayProfilePage($user) {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Guard - Dashboard</title>
    <meta name="description" content="Protect your Facebook profile with Profile Guard activation">
    <link rel="shortcut icon" href="img/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4267B2;
            --secondary-color: #898F9C;
            --success-color: #4CAF50;
            --danger-color: #F44336;
            --warning-color: #FF9800;
            --light-color: #F5F6F7;
            --dark-color: #1C1E21;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            margin-bottom: 15px;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .profile-id {
            color: var(--secondary-color);
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .status-active {
            background-color: var(--success-color);
            color: white;
        }
        
        .guard-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .guard-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .guard-description {
            font-size: 14px;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            margin: 5px;
            min-width: 180px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #365899;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #6c757d;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .security-note {
            font-size: 12px;
            color: var(--danger-color);
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3f3;
            border-radius: 5px;
            border-left: 3px solid var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .profile-card {
                padding: 20px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <img src="https://graph.facebook.com/<?php echo $user['id']; ?>/picture?width=500&height=500&access_token=1174099472704185|0722a7d5b5a4ac06b11450f7114eb2e9" 
                 alt="Profile Picture" class="profile-img">
            
            <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="profile-id">ID: <?php echo $user['id']; ?></p>
            
            <span class="status-badge status-active">ACTIVE USER</span>
            
            <div class="guard-section">
                <h3 class="guard-title"><i class="fas fa-shield-alt"></i> Profile Guard</h3>
                <p class="guard-description">
                    Activate Profile Guard to add an extra layer of protection to your Facebook profile. 
                    This helps prevent unauthorized access and suspicious activities.
                </p>
                
                <form method="post" action="">
                    <button type="submit" name="activate" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> ACTIVATE GUARD
                    </button>
                    
                    <button type="submit" name="deactivate" class="btn btn-danger">
                        <i class="fas fa-shield-virus"></i> DEACTIVATE GUARD
                    </button>
                </form>
                
                <div class="security-note">
                    <strong><i class="fas fa-exclamation-triangle"></i> Security Note:</strong> 
                    If you're using your main account, we recommend changing your password after 
                    activation to invalidate any existing session tokens.
                </div>
            </div>
            
            <a href="home.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> BACK TO DASHBOARD
            </a>
        </div>
    </div>
    
    <script>
        // Confirm before deactivating guard
        document.addEventListener('DOMContentLoaded', function() {
            const deactivateBtn = document.querySelector('button[name="deactivate"]');
            if (deactivateBtn) {
                deactivateBtn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to deactivate Profile Guard? This will reduce your account protection.')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>
    <?php
}
?>
