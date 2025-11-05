<?php
session_start();
require_once '../../lib/autoloader.php';
require_once '../../config.php';
require_once '../../database.php';
require_once '../../user.php';

$user_handler = new User($pdo);

$provider = new \Greew\OAuth2\Client\Provider\Azure([
    'clientId'                => $ssoConfig['clientId'],
    'clientSecret'            => $ssoConfig['clientSecret'],
    'redirectUri'             => $ssoConfig['redirectUri'],
    'urlAuthorize'            => "https://login.microsoftonline.com/{$ssoConfig['tenant']}/oauth2/v2.0/authorize",
    'urlAccessToken'          => "https://login.microsoftonline.com/{$ssoConfig['tenant']}/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => "https://graph.microsoft.com/v1.0/me",
    'scopes'                  => 'openid profile email',
]);

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {
    try {
        $token = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
        $resourceOwner = $provider->getResourceOwner($token);
        $sso_user = $resourceOwner->toArray();
        $sso_id = $sso_user['oid']; // Object ID from Azure
        $username = $sso_user['userPrincipalName'];

        // Check if user exists
        $user = $user_handler->findByUsername($username);

        if (!$user) {
            // User doesn't exist, create them with a default role (e.g., 'viewer')
            // You might want to make this more configurable
            $viewer_role = $pdo->query("SELECT id FROM user_roles WHERE name = 'viewer'")->fetchColumn();
            if (!$viewer_role) {
                // Handle case where 'viewer' role doesn't exist
                die("Default role 'viewer' not found. Please create it.");
            }

            // Create a new user with a random password since they will only log in via SSO
            $user_handler->createUser($username, bin2hex(random_bytes(16)), $viewer_role);
            $user = $user_handler->findByUsername($username);

            // Set SSO provider details
            $stmt = $pdo->prepare("UPDATE users SET sso_provider = 'office365', sso_id = ? WHERE id = ?");
            $stmt->execute([$sso_id, $user['id']]);
        }

        // Log the user in
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        // Failed to get user details
        exit('Something went wrong during SSO authentication: ' . $e->getMessage());
    }
}
