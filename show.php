<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$studentId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$student = false;
$databaseUnavailable = false;

if (!is_int($studentId) || $studentId < 1) {
    http_response_code(404);
} else {
    try {
        $statement = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on, created_at, updated_at
            FROM students
            WHERE id = :id'
        );
        $statement->execute(['id' => $studentId]);
        $student = $statement->fetch();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $databaseUnavailable = true;
        http_response_code(503);
    }

    if ($student === false && !$databaseUnavailable) {
        http_response_code(404);
    }
}

$pageTitle = $student !== false
    ? $student['first_name'] . ' ' . $student['last_name']
    : 'Student details';

require __DIR__ . '/partials/header.php';
?>
<?php if ($databaseUnavailable): ?>
    <section class="error-card">
        <h1>Student records are temporarily unavailable</h1>
        <p>We couldn't connect to the student database. Please make sure MySQL is running in XAMPP, then refresh this page.</p>
    </section>
<?php elseif ($student === false): ?>
    <section class="error-card">
        <h1>Student not found</h1>
        <p>The requested student record could not be found. <a href="index.php">Return to students</a>.</p>
    </section>
<?php else: ?>
    <section aria-labelledby="page-title">
        <div class="page-heading">
            <div>
                <h1 id="page-title"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
                <p class="page-description">Student record details.</p>
            </div>
            <a class="button button-primary" href="edit.php?id=<?= e($student['id']) ?>">Edit student</a>
        </div>

        <dl class="detail-card">
            <div class="detail-row">
                <dt>Email</dt>
                <dd><?= e($student['email']) ?></dd>
            </div>
            <div class="detail-row">
                <dt>Phone</dt>
                <dd><?= $student['phone'] !== null && $student['phone'] !== '' ? e($student['phone']) : 'Not provided' ?></dd>
            </div>
            <div class="detail-row">
                <dt>Course</dt>
                <dd><span class="course-badge"><?= e($student['course']) ?></span></dd>
            </div>
            <div class="detail-row">
                <dt>Enrolled on</dt>
                <dd><?= e((new DateTimeImmutable($student['enrolled_on']))->format('d M Y')) ?></dd>
            </div>
            <div class="detail-row">
                <dt>Record created</dt>
                <dd><?= e((new DateTimeImmutable($student['created_at']))->format('d M Y, H:i')) ?></dd>
            </div>
            <div class="detail-row">
                <dt>Last updated</dt>
                <dd><?= e((new DateTimeImmutable($student['updated_at']))->format('d M Y, H:i')) ?></dd>
            </div>
        </dl>
        <p><a href="index.php">← Back to students</a></p>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
