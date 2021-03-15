<?php
/** @var string $content */
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Attendance</title>
	<link href="/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

	<nav class="navbar navbar-expand navbar-dark bg-dark mb-3">
		<div class="container">
			<a class="navbar-brand" href="/">Attendance</a>
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" href="/">Dashboard</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Configuration</a>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="/departments">Departments</a></li>
						<li><a class="dropdown-item" href="/employees">Employees</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>

	<div class="container">
		<?= $content ?>
	</div>

	<script src="/js/bootstrap.bundle.min.js"></script>
	<script src="/js/feather.min.js"></script>
	<script>
		(function () {
			feather.replace();
		})();
	</script>

</body>

</html>
