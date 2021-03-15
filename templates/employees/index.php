<?php
/** @var array $employees */
?>

<?php require __DIR__ . "/../commons/success.php" ?>

<nav>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			Configuration
		</li>
		<li class="breadcrumb-item active">
			<a href="/employees">Employees</a>
		</li>
	</ol>
</nav>

<div class="d-flex justify-content-between align-items-center border-bottom mb-3">
	<h1>Employee list</h1>
	<a href="/employees/form" class="btn btn-primary align-baseline">Add new employee</a>
</div>

<table class="table table-hover">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($employees as $employee): ?>
			<tr>
				<td>
					<a href="/employees/view/<?= $employee->id ?>">
						<?= $employee->name ?>
					</a>
				</td>
				<td><?= $employee->email ?></td>
				<td><?= $employee->description ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>