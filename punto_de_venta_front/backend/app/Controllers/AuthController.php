<?php
namespace App\Controllers;
use App\Helpers\DB;

class AuthController {
    public function register() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $rfc = trim($_POST['rfc'] ?? '');
            if (!$name || !$email || !$pass) {
                $error = "Todos los campos obligatorios deben llenarse.";
            } else {
                $pdo = DB::get();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "El correo ya está registrado.";
                } else {
                    $ins = $pdo->prepare("INSERT INTO users (name, email, password, rfc) VALUES (?,?,?,?)");
                    $ins->execute([$name, $email, $pass, $rfc ?: 'XAXX010101000']);
                    $_SESSION['user'] = [
                        'id' => $pdo->lastInsertId(),
                        'name' => $name,
                        'email' => $email,
                        'is_admin' => 0
                    ];
                    $redirect = $_GET['redirect'] ?? 'home';
                    header('Location: index.php?page=' . urlencode($redirect));
                    exit;
                }
            }
        }
        include __DIR__ . '/../../../frontend/app/Views/auth/register.php';
    }
    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $pass = $_POST['password'] ?? '';
            $pdo = DB::get();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user && ($user['password'] === $pass)) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'is_admin' => $user['is_admin']
                ];
                $redirect = $_GET['redirect'] ?? 'home';
                header('Location: index.php?page=' . urlencode($redirect));
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        }
        include __DIR__ . '/../../../frontend/app/Views/auth/login.php';
    }

    public function logout() {
        unset($_SESSION['user']);
        header('Location: index.php');
        exit;
    }
}
