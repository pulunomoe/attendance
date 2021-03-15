<?php
/** @var \App\Entity\Department $department */
?>

<?php require __DIR__ . "/../commons/success.php" ?>
<?php require __DIR__ . "/../commons/errors.php" ?>

<ul class="breadcrumb text-gray">
	<li class="breadcrumb-item">
		<a href="/departments">Departments</a>
	</li>
	<li class="breadcrumb-item">
		<a href="/departments/view/<?= $department->id ?>"><?= $department->name ?></a>
	</li>
</ul>

<div class="divider"></div>

<h1>
	<?= $department->name ?>
	<div class="float-right text-tiny">
		<a href="/departments" class="btn btn-sm">Back to list</a>
		<a href="/departments/form/<?= $department->id ?>" class="btn btn-sm btn-success">Edit</a>
		<form action="/departments/delete" method="post" id="delete-department-form" class="form-inline">
			<input type="hidden" name="id" value="<?= $department->id ?>">
			<button type="submit" class="btn btn-sm btn-error">Delete</button>
		</form>
	</div>
</h1>

<?= $department->description ?>

<script>
    let deleteDepartmentForm = document.querySelector('form#delete-department-form');
    deleteDepartmentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (confirm('Are you sure?')) {
            deleteDepartmentForm.submit();
        }
    });
</script>
