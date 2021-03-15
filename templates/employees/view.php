<?php
/** @var \App\Entity\Employee $employee */
?>

<?php require __DIR__ . "/../commons/success.php" ?>
<?php require __DIR__ . "/../commons/errors.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item">
			<a href="/employees">Employees</a>
		</li>
		<li class="breadcrumb-item active">
			<a href="/employees/view/<?= $employee->id ?>"><?= $employee->name ?> &lt;<?= $employee->email ?>&gt;</a>
		</li>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1><?= $employee->name ?> <small class="text-muted">&lt;<?= $employee->email ?>&gt;</small></h1>
	<div class="btn-group">
		<a href="/employees" class="btn btn-outline-secondary">Back to list</a>
		<a href="/employees/form/<?= $employee->id ?>" class="btn btn-info">Edit</a>
		<button class="btn btn-danger" id="delete-employee">Delete</button>
	</div>
</div>

<?= $employee->description ?>

<form action="/employees/delete" method="post" id="delete-employee-form" class="d-none">
	<input type="hidden" name="id" value="<?= $employee->id ?>">
</form>

<script>
    document.querySelector('button#delete-employee').addEventListener('click', function (e) {
        if (confirm('Are you sure?')) {
            document.querySelector('form#delete-employee-form').submit();
        }
    });
</script>
