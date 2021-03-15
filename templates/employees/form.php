<?php
require __DIR__ . '/../__helpers/form_helpers.php';

/** @var \App\Entity\Employee $employee */
?>

<?php require __DIR__ . "/../commons/errors.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item">
			<a href="/employees">Employees</a>
		</li>
		<?php if (empty($employee)): ?>
			<li class="breadcrumb-item">
				<a href="/employees/form">Add</a>
			</li>
		<?php else: ?>
			<li class="breadcrumb-item active">
				<a href="/employees/view/<?= $employee->id ?>"><?= $employee->name ?></a>
			</li>
			<li class="breadcrumb-item active">
				<a href="/employees/form/<?= $employee->id ?>">Edit</a>
			</li>
		<?php endif; ?>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1><?= empty($employee) ? 'Add new' : 'Edit' ?> employee</h1>
	<?php if (empty($employee)): ?>
		<a href="/employees" class="btn btn-outline-secondary">Back to list</a>
	<?php else: ?>
		<a href="/employees/view/<?= $employee->id ?>" class="btn btn-outline-secondary">Back to view</a>
	<?php endif; ?>
</div>

<form method="post" action="/employees/form">
	<?php if (!empty($employee)): ?>
		<input type="hidden" name="id" value="<?= $employee->id ?>">
	<?php endif; ?>
	<?= formInput('name', 'Name') ?>
	<?= formInput('email', 'E-mail', 'email') ?>
	<?php if (empty($employee)): ?>
		<?= formInput('password', 'Password', 'password') ?>
		<?= formInput('confirm', 'Confirm password', 'password') ?>
	<?php endif; ?>
	<?= formTextarea('description', 'Description') ?>
	<?= formSubmit() ?>
</form>

<?php if (!empty($employee)): ?>
	<script>
		document.querySelector('input[name="name"]').value = '<?= $employee->name ?>';
		document.querySelector('input[name="email"]').value = '<?= $employee->email ?>';
		document.querySelector('textarea[name="description"]').value = '<?= $employee->description ?>';
	</script>
<?php endif; ?>
