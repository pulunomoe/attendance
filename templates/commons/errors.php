<?php if (!empty($errors)): ?>
	<div class="alert alert-success alert-dismissible">
		Unable to save the data, please fix the following errors:<br>
		<?php foreach ($errors as $error): ?>
			&bull; <?= $error ?><br>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

