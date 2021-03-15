<?php
require __DIR__ . '/../__helpers/form_helpers.php';

/** @var \App\Entity\Department $department */
?>

<?php require __DIR__ . "/../commons/errors.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item">
			<a href="/departments">Departments</a>
		</li>
		<?php if (empty($department)): ?>
			<li class="breadcrumb-item">
				<a href="/departments/form">Add</a>
			</li>
		<?php else: ?>
			<li class="breadcrumb-item active">
				<a href="/departments/view/<?= $department->id ?>"><?= $department->name ?></a>
			</li>
			<li class="breadcrumb-item active">
				<a href="/departments/form/<?= $department->id ?>">Edit</a>
			</li>
		<?php endif; ?>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1><?= empty($department) ? 'Add new' : 'Edit' ?> department</h1>
	<?php if (empty($department)): ?>
		<a href="/departments" class="btn btn-outline-secondary">Back to list</a>
	<?php else: ?>
		<a href="/departments/view/<?= $department->id ?>" class="btn btn-outline-secondary">Back to view</a>
	<?php endif; ?>
</div>

<form method="post" action="/departments/form">
	<?php if (!empty($department)): ?>
		<input type="hidden" name="id" value="<?= $department->id ?>">
	<?php endif; ?>
	<?= formInput('name', 'Name') ?>
	<?= formTextarea('description', 'Description') ?>
	<?= formSubmit() ?>
</form>

<?php if (!empty($department)): ?>
	<script>
		document.querySelector('input[name="name"]').value = '<?= $department->name ?>';
		document.querySelector('textarea[name="description"]').value = '<?= $department->description ?>';
	</script>
<?php endif; ?>
