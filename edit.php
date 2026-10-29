<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/student-validation.php';

$pageTitle = 'Edit student';
$studentId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$student = [];
$errors = [];
$databaseUnavailable = false;

if (!is_int($studentId) || $studentId < 1) {
    http_response_code(404);
} else {
    try {
        $pdo = db();
        $statement = $pdo->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on
            FROM students
            WHERE id = :id'
        );
        $statement->execute(['id' => $studentId]);
        $student = $statement->fetch() ?: [];
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $databaseUnavailable = true;
        http_response_code(503);
    }

    if ($student === [] && !$databaseUnavailable) {
        http_response_code(404);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student !== []) {
    foreach (['first_name', 'last_name', 'email', 'phone', 'course', 'enrolled_on'] as $field) {
        $value = $_POST[$field] ?? '';
        $student[$field] = is_string($value) ? trim($value) : '';
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errors['_form'] = 'Your session expired or the form could not be verified. Please try again.';
    } else {
        try {
            $errors = validateStudent($pdo, $student, $studentId);

            if ($errors === []) {
                $statement = $pdo->prepare(
                    'UPDATE students
                    SET first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        phone = :phone,
                        course = :course,
                        enrolled_on = :enrolled_on
                    WHERE id = :id'
                );
                $statement->execute([
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'email' => $student['email'],
                    'phone' => $student['phone'] === '' ? null : $student['phone'],
                    'course' => $student['course'],
                    'enrolled_on' => $student['enrolled_on'],
                    'id' => $studentId,
                ]);

                flash('success', 'Student updated');
                redirect('index.php');
            }
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            if (isDuplicateEmailException($exception)) {
                $errors['email'] = 'A student with this email address already exists.';
            } else {
                http_response_code(503);
                $errors['_form'] = 'We could not save this student right now. Please check that MySQL is running and try again.';
            }
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<?php if ($databaseUnavailable): ?>
    <section class="error-card">
        <h1>Student records are temporarily unavailable</h1>
        <p>We couldn't connect to the student database. Please make sure MySQL is running in XAMPP, then refresh this page.</p>
    </section>
<?php elseif ($student === []): ?>
    <section class="error-card">
        <h1>Student not found</h1>
        <p>The requested student record could not be found. <a href="index.php">Return to students</a>.</p>
    </section>
<?php else: ?>
    <section aria-labelledby="page-title">
        <div class="page-heading">
            <div>
                <h1 id="page-title">Edit student</h1>
                <p class="page-description">Update the student's details below.</p>
            </div>
        </div>
        <?php
        $formAction = 'edit.php?id=' . rawurlencode((string) $studentId);
        $submitLabel = 'Save changes';
        require __DIR__ . '/partials/student-form.php';
        ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
