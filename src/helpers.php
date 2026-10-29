<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'msg' => $msg,
    ];
}

function getFlash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($message) ? $message : null;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function studentCourses(): array
{
    return [
        'Web Development',
        'Software Engineering',
        'Data Analysis',
        'UI/UX Design',
    ];
}
