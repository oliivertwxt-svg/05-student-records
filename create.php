<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/student-validation.php';

$pageTitle = 'Add student';
$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'enrolled_on' => date('Y-m-d'),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($student) as $field) {
        $value = $_POST[$field] ?? '';
        $student[$field] = is_string($value) ? trim($value) : '';
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errors['_form'] = 'Your session expired or the form could not be verified. Please try again.';
    } else {
        try {
            $pdo = db();
            $errors = validateStudent($pdo, $student);

            if ($errors === []) {
                $statement = $pdo->prepare(
                    'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                    VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
                );
                $statement->execute([
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'email' => $student['email'],
                    'phone' => $student['phone'] === '' ? null : $student['phone'],
                    'course' => $student['course'],
                    'enrolled_on' => $student['enrolled_on'],
                ]);

                flash('success', 'Student added');
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
<section aria-labelledby="page-title">
    <div class="page-heading">
        <div>
            <h1 id="page-title">Add student</h1>
            <p class="page-description">Enter the student's details below.</p>
        </div>
    </div>
    <?php
    $formAction = 'create.php';
    $submitLabel = 'Add student';
    require __DIR__ . '/partials/student-form.php';
    ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
