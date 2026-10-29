<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('The request could not be verified. Return to the student list and try again.');
}

$studentId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!is_int($studentId) || $studentId < 1) {
    http_response_code(400);
    exit('A valid student ID is required.');
}

try {
    $statement = db()->prepare('DELETE FROM students WHERE id = :id');
    $statement->execute(['id' => $studentId]);

    if ($statement->rowCount() === 0) {
        http_response_code(404);
        exit('Student not found.');
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Student records are temporarily unavailable. Please check that MySQL is running and try again.');
}

flash('success', 'Student deleted');
redirect('index.php');
