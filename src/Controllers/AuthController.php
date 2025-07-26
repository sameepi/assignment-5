<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;
use App\Views\View;

final class AuthController
{
    private $user;
    private $view;

    public function __construct()
    {
        $this->user = new User();
        $this->view = new View();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $this->user->login($email, $password);
            
            if ($user) {
                Session::set('user_id', $user['id']);
                Session::set('username', $user['first_name'] . ' ' . $user['last_name']);
                Session::set('email', $user['email']);
                
                header('Location: ' . url('/'));
                exit;
            } else {
                $error = 'Invalid email or password';
                $this->view->render('auth/login', [
                    'pageTitle' => 'Login',
                    'error' => $error,
                    'email' => $email
                ]);
            }
        } else {
            $this->view->render('auth/login', [
                'pageTitle' => 'Login'
            ]);
        }
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            // Validation
            if (empty($first_name)) $errors[] = 'First name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
            if (empty($password)) $errors[] = 'Password is required';
            if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
            
            // Check if email already exists
            if ($this->user->findByEmail($email)) {
                $errors[] = 'Email already registered';
            }
            
            if (empty($errors)) {
                if ($this->user->register($first_name, $last_name, $email, $password)) {
                    Session::setFlash('success', 'Registration successful! Please log in.');
                    header('Location: ' . url('/login'));
                    exit;
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
            
            $this->view->render('auth/register', [
                'pageTitle' => 'Register',
                'errors' => $errors,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email
            ]);
        } else {
            $this->view->render('auth/register', [
                'pageTitle' => 'Register'
            ]);
        }
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: ' . url('/'));
        exit;
    }
}
