<?php
/** @var \App\Entity\Department $department */
?>

<?php require __DIR__ . "/../commons/success.php" ?>
<?php require __DIR__ . "/../commons/errors.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item">
			<a href="/departments">Departments</a>
		</li>
		<li class="breadcrumb-item active">
			<a href="/departments/view/<?= $department->id ?>"><?= $department->name ?></a>
		</li>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1><?= $department->name ?></h1>
	<div class="btn-group">
		<a href="/departments" class="btn btn-outline-secondary">Back to list</a>
		<a href="/departments/form/<?= $department->id ?>" class="btn btn-info">Edit</a>
		<button class="btn btn-danger" id="delete-department">Delete</button>
	</div>
</div>

<?= $department->description ?>

<ul class="nav nav-tabs mt-5 mb-3">
	<li class="nav-item">
		<button class="nav-link active" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees-tab-content">Employees</button>
	</li>
	<li class="nav-item">
		<button class="nav-link" id="holidays-tab" data-bs-toggle="tab" data-bs-target="#holidays-tab-content">Holidays</button>
	</li>
	<li class="nav-item">
		<button class="nav-link" id="statuses-tab" data-bs-toggle="tab" data-bs-target="#statuses-tab-content">Statues</button>
	</li>
</ul>

<div class="tab-content">

	<div class="tab-pane show active" id="employees-tab-content">

		<table class="table table-hover">
			<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Role</th>
				<th>Start date</th>
				<th>End date</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($employees as $employee): ?>
				<tr>
					<td>
						<a href="/departments/view/<?= $deparment->id ?>">
							<?= $deparment->name ?>
						</a>
					</td>
					<td>Items</td>
					<td><?= $deparment->description ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<a href="/departments/form" class="btn btn-primary">Assign employee</a>

	</div>

	<div class="tab-pane" id="holidays-tab-content">
		WOWS
	</div>

	<div class="tab-pane" id="statuses-tab-content">
		WOWZER
	</div>

</div>

<form action="/departments/delete" method="post" id="delete-department-form" class="d-none">
	<input type="hidden" name="id" value="<?= $department->id ?>">
</form>

<script>
    document.querySelector('button#delete-department').addEventListener('click', function (e) {
        if (confirm('Are you sure?')) {
            document.querySelector('form#delete-department-form').submit();
        }
    });
</script>
