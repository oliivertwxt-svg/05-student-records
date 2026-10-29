<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';
$databaseUnavailable = false;
$students = [];
$courses = studentCourses();
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$selectedCourse = isset($_GET['course']) && is_string($_GET['course']) ? $_GET['course'] : '';
if (!in_array($selectedCourse, $courses, true)) {
    $selectedCourse = '';
}

$perPage = 10;
$requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$currentPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
$totalStudents = 0;
$totalPages = 1;
$startNumber = 0;
$endNumber = 0;

try {
    $pdo = db();
    $conditions = [];
    $parameters = [];

    if ($search !== '') {
        $conditions[] = '(first_name LIKE :first_name OR last_name LIKE :last_name OR email LIKE :email)';
        $searchPattern = '%' . $search . '%';
        $parameters['first_name'] = $searchPattern;
        $parameters['last_name'] = $searchPattern;
        $parameters['email'] = $searchPattern;
    }

    if ($selectedCourse !== '') {
        $conditions[] = 'course = :course';
        $parameters['course'] = $selectedCourse;
    }

    $whereClause = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
    $countStatement = $pdo->prepare('SELECT COUNT(*) FROM students' . $whereClause);
    $countStatement->execute($parameters);
    $totalStudents = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    $listStatement = $pdo->prepare(
        'SELECT id, first_name, last_name, email, course, enrolled_on
        FROM students' . $whereClause . '
        ORDER BY last_name, first_name
        LIMIT :limit OFFSET :offset'
    );
    foreach ($parameters as $name => $value) {
        $listStatement->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }
    $listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStatement->execute();
    $students = $listStatement->fetchAll();

    if ($totalStudents > 0) {
        $startNumber = $offset + 1;
        $endNumber = min($offset + count($students), $totalStudents);
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $databaseUnavailable = true;
    http_response_code(503);
}

$paginationParameters = [];
if ($search !== '') {
    $paginationParameters['q'] = $search;
}
if ($selectedCourse !== '') {
    $paginationParameters['course'] = $selectedCourse;
}
$paginationUrl = static function (int $page) use ($paginationParameters): string {
    return 'index.php?' . http_build_query($paginationParameters + ['page' => $page]);
};

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

        <form class="filter-form" method="get" action="index.php">
            <div class="filter-field">
                <label for="q">Search students</label>
                <input id="q" name="q" type="search" value="<?= e($search) ?>"
                    placeholder="Name or email">
            </div>
            <div class="filter-field">
                <label for="course">Course</label>
                <select id="course" name="course">
                    <option value="">All courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course) ?>" <?= $selectedCourse === $course ? 'selected' : '' ?>>
                            <?= e($course) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="button button-secondary" type="submit">Apply filters</button>
            <?php if ($search !== '' || $selectedCourse !== ''): ?>
                <a class="clear-filters" href="index.php">Clear filters</a>
            <?php endif; ?>
        </form>

        <p class="results-summary">
            Showing <?= $startNumber ?>–<?= $endNumber ?> of <?= $totalStudents ?>
            <?= $totalStudents === 1 ? 'student' : 'students' ?>
        </p>

        <div class="table-card">
            <?php if ($students === []): ?>
                <div class="empty-state">
                    <strong>No students found</strong>
                    <?= $search !== '' || $selectedCourse !== '' ? 'Try adjusting your search or course filter.' : 'Add a student to get started.' ?>
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

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Student pages">
                <?php if ($currentPage > 1): ?>
                    <a class="page-link" href="<?= e($paginationUrl($currentPage - 1)) ?>" rel="prev">Previous</a>
                <?php endif; ?>
                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                    <?php if ($page === $currentPage): ?>
                        <span class="page-link is-current" aria-current="page"><?= $page ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= e($paginationUrl($page)) ?>"><?= $page ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a class="page-link" href="<?= e($paginationUrl($currentPage + 1)) ?>" rel="next">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
