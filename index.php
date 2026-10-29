<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';
$databaseUnavailable = false;
$students = [];

try {
    $students = db()
        ->query('SELECT id, first_name, last_name, email, course, enrolled_on FROM students ORDER BY last_name, first_name')
        ->fetchAll();
} catch (PDOException $exception) {
    $databaseUnavailable = true;
    http_response_code(503);
}

require __DIR__ . '/partials/header.php';
?>
<?php if ($databaseUnavailable): ?>
    <section class="error-card">
        <h1>Student records are temporarily unavailable</h1>
        <p>We couldn't connect to the student database. Please make sure MySQL is running in XAMPP, then refresh this page.</p>
    </section>
<?php else: ?>
    <section aria-labelledby="page-title">
        <div class="page-heading">
            <div>
                <h1 id="page-title">Students</h1>
                <p class="page-description">View and manage student records.</p>
            </div>
            <a class="button button-primary" href="create.php">Add student</a>
        </div>

        <div class="table-card">
            <?php if ($students === []): ?>
                <div class="empty-state">
                    <strong>No students yet</strong>
                    Add a student to get started.
                </div>
            <?php else: ?>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Course</th>
                                <th scope="col">Enrolled</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="student-name">
                                        <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </td>
                                    <td><?= e($student['email']) ?></td>
                                    <td><span class="course-badge"><?= e($student['course']) ?></span></td>
                                    <td>
                                        <?php
                                        $enrolledOn = new DateTimeImmutable($student['enrolled_on']);
                                        echo e($enrolledOn->format('d M Y'));
                                        ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="show.php?id=<?= e($student['id']) ?>">View</a>
                                            <a href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                            <form method="post" action="delete.php"
                                                onsubmit="return confirm(<?= e(json_encode(
                                                    'Delete ' . $student['first_name'] . ' ' . $student['last_name'] . '?',
                                                    JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
                                                )) ?>);">
                                                <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                <button class="action-delete" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
