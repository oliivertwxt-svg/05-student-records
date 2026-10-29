<?php

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/helpers.php';

if (($_GET['confirm'] ?? null) !== 'yes') {
    http_response_code(400);
    exit('To insert 50 sample students, open seed.php?confirm=yes.');
}

$students = [
    ['Amina', 'Adeyemi'],
    ['Chinedu', 'Okafor'],
    ['Ngozi', 'Eze'],
    ['Tunde', 'Bello'],
    ['Fatima', 'Abubakar'],
    ['Emeka', 'Nwosu'],
    ['Ifeoma', 'Okeke'],
    ['Oluwaseun', 'Adeleke'],
    ['Zainab', 'Musa'],
    ['Chukwuemeka', 'Obi'],
    ['Kemi', 'Balogun'],
    ['Yusuf', 'Ibrahim'],
    ['Adaeze', 'Onyeka'],
    ['Babatunde', 'Ojo'],
    ['Hadiza', 'Sani'],
    ['Obinna', 'Eze'],
    ['Amaka', 'Nwachukwu'],
    ['Damilola', 'Ogunleye'],
    ['Maryam', 'Lawal'],
    ['Ikenna', 'Umeh'],
    ['Temitope', 'Fashola'],
    ['Aisha', 'Yusuf'],
    ['Chiamaka', 'Anyanwu'],
    ['Olumide', 'Olowe'],
    ['Halima', 'Garba'],
    ['Nnamdi', 'Ibe'],
    ['Uchechi', 'Opara'],
    ['Ayodeji', 'Akinola'],
    ['Safiya', 'Bala'],
    ['Kelechi', 'Maduka'],
    ['Bisi', 'Alabi'],
    ['Sulaiman', 'Abdullahi'],
    ['Ogechi', 'Okonkwo'],
    ['Femi', 'Adewale'],
    ['Maimuna', 'Shehu'],
    ['Chibuzo', 'Iro'],
    ['Nneka', 'Ekwueme'],
    ['Tobi', 'Oladipo'],
    ['Rukayat', 'Adamu'],
    ['Somto', 'Ezenwa'],
    ['Folake', 'Ogunbiyi'],
    ['Mustapha', 'Bako'],
    ['Chisom', 'Ude'],
    ['Tosin', 'Afolabi'],
    ['Aminat', 'Sule'],
    ['Ejike', 'Nnaji'],
    ['Onyinye', 'Uwakwe'],
    ['Kayode', 'Ogun'],
    ['Bilkisu', 'Tanko'],
    ['Ifeanyi', 'Chukwu'],
];
$courses = studentCourses();
$startDate = new DateTimeImmutable('2020-01-01');
$daysAvailable = (int) $startDate->diff(new DateTimeImmutable('today'))->format('%a') + 1;
$batchId = bin2hex(random_bytes(6));

try {
    $pdo = db();
    $pdo->beginTransaction();
    $statement = $pdo->prepare(
        'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
        VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
    );

    foreach ($students as $index => [$firstName, $lastName]) {
        $number = $index + 1;
        $phone = $number % 3 === 0
            ? null
            : sprintf('0%d%d%08d', 7 + ($number % 2), $number % 2, 10000000 + $number);
        $enrolledOn = $startDate->modify('+' . (($index * 47) % $daysAvailable) . ' days');

        $statement->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => sprintf('sample-%s-%02d@example.test', $batchId, $number),
            'phone' => $phone,
            'course' => $courses[$index % count($courses)],
            'enrolled_on' => $enrolledOn->format('Y-m-d'),
        ]);
    }

    $pdo->commit();
    echo 'Inserted 50 sample students. Remove this seed script when finished testing.';
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($exception->getMessage());
    http_response_code(503);
    exit('Could not insert sample students. Check the PHP error log and database connection.');
}
