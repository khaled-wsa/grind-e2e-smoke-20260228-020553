<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

function render_page(string $title, string $content): void
{
    $flash = pull_flash();
    $flashHtml = '';

    if (is_array($flash)) {
        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';
        $class = $type === 'error' ? 'error' : 'success';
        $flashHtml = '<p class="flash ' . e($class) . '">' . e((string) $message) . '</p>';
    }

    echo '<!doctype html>';
    echo '<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . '</title>';
    echo '<style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;max-width:720px;margin:2rem auto;padding:0 1rem;line-height:1.4}
        form{display:grid;gap:.75rem;max-width:360px}
        input{padding:.55rem;border:1px solid #ccc;border-radius:6px}
        button{padding:.6rem .75rem;border:0;border-radius:6px;background:#1f5fbf;color:#fff;cursor:pointer}
        .flash{padding:.65rem .8rem;border-radius:6px}
        .flash.error{background:#ffeaea;color:#6b1313}
        .flash.success{background:#e8f7ea;color:#14532d}
        .card{padding:1rem;border:1px solid #e1e1e1;border-radius:8px}
    </style></head><body>';
    echo '<h1>' . e($title) . '</h1>';
    echo $flashHtml;
    echo $content;
    echo '</body></html>';
}

function require_post_with_csrf(): void
{
    if (request_method() !== 'POST' || !validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        render_page('Bad Request', '<p>Invalid form submission.</p><p><a href="/login">Back to login</a></p>');
        exit;
    }
}

$path = request_path();
$method = request_method();

if ($path === '/' && $method === 'GET') {
    redirect(is_authenticated() ? '/dashboard' : '/login');
}

if ($path === '/register' && $method === 'GET') {
    $content = '<form method="post" action="/register">
        <input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">
        <label>Email <input type="email" name="email" required maxlength="190"></label>
        <label>Password <input type="password" name="password" required minlength="8"></label>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="/login">Sign in</a></p>';
    render_page('Register', $content);
    exit;
}

if ($path === '/register' && $method === 'POST') {
    require_post_with_csrf();

    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $errors = validate_registration_input($email, $password);

    if ($errors !== []) {
        set_flash('error', $errors[0]);
        redirect('/register', 303);
    }

    if (user_exists($email)) {
        set_flash('error', 'Unable to create account with those details.');
        redirect('/register', 303);
    }

    if (!create_user($email, $password)) {
        set_flash('error', 'Unable to create account right now.');
        redirect('/register', 303);
    }

    set_flash('success', 'Account created. Sign in to continue.');
    redirect('/login', 303);
}

if ($path === '/login' && $method === 'GET') {
    if (is_authenticated()) {
        redirect('/dashboard');
    }

    $content = '<form method="post" action="/login">
        <input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">
        <label>Email <input type="email" name="email" required maxlength="190"></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Sign in</button>
    </form>
    <p>Need an account? <a href="/register">Register</a></p>';
    render_page('Login', $content);
    exit;
}

if ($path === '/login' && $method === 'POST') {
    require_post_with_csrf();

    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL) || $password === '') {
        set_flash('error', 'Invalid email or password.');
        redirect('/login', 303);
    }

    if (!verify_user_credentials($email, $password)) {
        set_flash('error', 'Invalid email or password.');
        redirect('/login', 303);
    }

    login_user($email);
    set_flash('success', 'Signed in successfully.');
    redirect('/dashboard', 303);
}

if ($path === '/logout' && $method === 'POST') {
    require_post_with_csrf();
    logout_user();
    set_flash('success', 'Signed out.');
    redirect('/login', 303);
}

if ($path === '/dashboard' && $method === 'GET') {
    require_authentication();
    $email = current_user_email() ?? '';
    $content = '<div class="card"><p>You are signed in as <strong>' . e($email) . '</strong>.</p>
        <form method="post" action="/logout">
            <input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">
            <button type="submit">Logout</button>
        </form></div>';
    render_page('Dashboard', $content);
    exit;
}

http_response_code(404);
render_page('Not Found', '<p>The requested page does not exist.</p>');

