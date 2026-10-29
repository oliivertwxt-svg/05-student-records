<?php

function validateStudent(PDO $pdo, array $student, ?int $excludeId = null): array
{
    $errors = [];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        $value = $student[$field] ?? '';

        if ($value === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (preg_match('/\A[\p{L} -]{2,60}\z/u', $value) !== 1) {
            $errors[$field] = $label . ' must be 2–60 letters, spaces, or hyphens.';
        }
    }

    $email = $student['email'] ?? '';
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    } elseif (strlen($email) > 120) {
        $errors['email'] = 'Email must be 120 characters or fewer.';
    }

    $phone = $student['phone'] ?? '';
    if ($phone !== '' && preg_match('/\A0[789][01]\d{8}\z/', $phone) !== 1) {
        $errors['phone'] = 'Enter a valid phone number or leave this field blank.';
    }

    if (!in_array($student['course'] ?? '', studentCourses(), true)) {
        $errors['course'] = 'Select a valid course.';
    }

    $enrolledOn = $student['enrolled_on'] ?? '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $enrolledOn);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $date === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $date->format('Y-m-d') !== $enrolledOn
        || $enrolledOn > date('Y-m-d')
    ) {
        $errors['enrolled_on'] = 'Enter a valid enrollment date that is not in the future.';
    }

    if ($errors === []) {
        $sql = 'SELECT id FROM students WHERE email = :email';
        $parameters = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $excludeId;
        }

        $statement = $pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        if ($statement->fetch() !== false) {
            $errors['email'] = 'A student with this email address already exists.';
        }
    }

    return $errors;
}

function isDuplicateEmailException(PDOException $exception): bool
{
    return $exception->getCode() === '23000'
        && isset($exception->errorInfo[1])
        && (int) $exception->errorInfo[1] === 1062;
}
