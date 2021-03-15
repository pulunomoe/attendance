<?php
/** @var array $departments */
?>

<?php require __DIR__ . "/../commons/success.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item active">
			<a href="/departments">Departments</a>
		</li>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1>Department list</h1>
	<a href="/departments/form" class="btn btn-primary align-baseline">Add new department</a>
</div>

<table class="table table-hover">
	<thead>
		<tr>
			<th>Name</th>
			<th>Employees</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($departments as $department): ?>
			<tr>
				<td>
					<a href="/departments/view/<?= $department->id ?>">
						<?= $department->name ?>
					</a>
				</td>
				<td>Items</td>
				<td><?= $department->description ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>