<?php
require_once 'config.php';

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    $pad = 4 - (strlen($data) % 4);
    if ($pad < 4) {
        $data .= str_repeat('=', $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateJWT($user) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // Token valid for 24 hours
    ]);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function validateJWT($jwt) {
    if (!$jwt) {
        return false;
    }
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return false;
    }
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $expectedSignature = base64UrlEncode($signature);

    if (!hash_equals($expectedSignature, $base64UrlSignature)) {
        return false;
    }

    $payload = json_decode(base64UrlDecode($base64UrlPayload), true);

    if ($payload['exp'] < time()) {
        return false; // Token expired
    }

    return $payload;
}

function setAuthToken($token) {
    setcookie('auth_token', $token, [
        'expires' => time() + (60 * 60 * 24),
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clearAuthToken() {
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password, role, full_name, email FROM users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $token = generateJWT($user);
            setAuthToken($token);
            // Set session user_id for uniform access in pages
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    }
    return false;
}

function logout() {
    clearAuthToken();
    // Clear session user_id
    unset($_SESSION['user_id']);
    session_destroy();
    header("Location: index.php");
    exit;
}

function getCurrentUser() {
    if (!isset($_COOKIE['auth_token'])) {
        return null;
    }
    $payload = validateJWT($_COOKIE['auth_token']);
    return $payload ?: null;
}

function isLoggedIn() {
    return getCurrentUser() !== null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}

function requireRole($role) {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $role) {
        header("Location: index.php");
        exit;
    }
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}
?>
