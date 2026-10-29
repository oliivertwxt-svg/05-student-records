<?php

$formAction = $formAction ?? '';
$submitLabel = $submitLabel ?? 'Save student';
?>
<?php if (isset($errors['_form'])): ?>
    <div class="alert alert-error" role="alert"><?= e($errors['_form']) ?></div>
<?php endif; ?>

<form class="form-card" method="post" action="<?= e($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="form-grid">
        <div class="form-field">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" minlength="2" maxlength="60" required
                value="<?= e($student['first_name'] ?? '') ?>"
                <?= isset($errors['first_name']) ? 'aria-invalid="true" aria-describedby="first_name-error"' : '' ?>>
            <?php if (isset($errors['first_name'])): ?>
                <p class="field-error" id="first_name-error"><?= e($errors['first_name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" minlength="2" maxlength="60" required
                value="<?= e($student['last_name'] ?? '') ?>"
                <?= isset($errors['last_name']) ? 'aria-invalid="true" aria-describedby="last_name-error"' : '' ?>>
            <?php if (isset($errors['last_name'])): ?>
                <p class="field-error" id="last_name-error"><?= e($errors['last_name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="120" required
                value="<?= e($student['email'] ?? '') ?>"
                <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
            <?php if (isset($errors['email'])): ?>
                <p class="field-error" id="email-error"><?= e($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="phone">Phone <span class="optional">(optional)</span></label>
            <input id="phone" name="phone" type="tel" inputmode="numeric" pattern="0[789][01][0-9]{8}"
                value="<?= e($student['phone'] ?? '') ?>"
                <?= isset($errors['phone']) ? 'aria-invalid="true" aria-describedby="phone-error"' : '' ?>>
            <?php if (isset($errors['phone'])): ?>
                <p class="field-error" id="phone-error"><?= e($errors['phone']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="course">Course</label>
            <select id="course" name="course" required
                <?= isset($errors['course']) ? 'aria-invalid="true" aria-describedby="course-error"' : '' ?>>
                <option value="">Select a course</option>
                <?php foreach (studentCourses() as $course): ?>
                    <option value="<?= e($course) ?>" <?= ($student['course'] ?? '') === $course ? 'selected' : '' ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course'])): ?>
                <p class="field-error" id="course-error"><?= e($errors['course']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="enrolled_on">Enrolled on</label>
            <input id="enrolled_on" name="enrolled_on" type="date" max="<?= e(date('Y-m-d')) ?>" required
                value="<?= e($student['enrolled_on'] ?? '') ?>"
                <?= isset($errors['enrolled_on']) ? 'aria-invalid="true" aria-describedby="enrolled_on-error"' : '' ?>>
            <?php if (isset($errors['enrolled_on'])): ?>
                <p class="field-error" id="enrolled_on-error"><?= e($errors['enrolled_on']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="index.php">Cancel</a>
        <button class="button button-primary" type="submit"><?= e($submitLabel) ?></button>
    </div>
</form>
