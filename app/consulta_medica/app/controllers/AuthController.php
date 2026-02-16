<?php
/**
 * Controlador de Autenticación
 * Sistema de Atención Médica - Consulta Externa
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Usuario;

class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Página de inicio - redirige según estado de autenticación
     */
    public function index(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('dashboard');
        } else {
            $this->redirect('login');
        }
    }

    /**
     * Mostrar formulario de login
     */
    public function login(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('dashboard');
            return;
        }

        $this->view('auth/login', [
            'title' => 'Iniciar Sesión',
            'csrf_token' => Session::getCsrfToken()
        ]);
    }

    /**
     * Procesar autenticación
     */
    public function authenticate(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('login');
            return;
        }

        // Validar token CSRF
        $csrfToken = $this->getPost('csrf_token');
        if (!Session::validateCsrfToken($csrfToken)) {
            set_flash('error', 'Token de seguridad inválido. Por favor, intente nuevamente.');
            $this->redirect('login');
            return;
        }

        $username = $this->getPost('username');
        $password = $_POST['password'] ?? ''; // No sanitizar password

        // Validar campos
        $validator = new Validator(['username' => $username, 'password' => $password]);
        $validator->validate([
            'username' => 'required|min:3',
            'password' => 'required|min:6'
        ]);

        if ($validator->hasErrors()) {
            set_flash('error', 'Por favor, complete todos los campos correctamente.');
            $this->redirect('login');
            return;
        }

        // Intentar autenticación
        $user = $this->usuarioModel->authenticate($username, $password);

        if (!$user) {
            app_log('warning', 'Intento de login fallido', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            set_flash('error', 'Usuario o contraseña incorrectos.');
            $this->redirect('login');
            return;
        }

        // Login exitoso
        Session::setUser($user);
        app_log('info', 'Login exitoso', ['user_id' => $user['id'], 'username' => $user['username']]);
        
        set_flash('success', 'Bienvenido, ' . $user['nombre_completo']);
        $this->redirect('dashboard');
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        $user = Session::getUser();
        
        if ($user) {
            app_log('info', 'Logout', ['user_id' => $user['id'], 'username' => $user['username']]);
        }

        Session::logout();
        set_flash('info', 'Ha cerrado sesión correctamente.');
        $this->redirect('login');
    }
}
