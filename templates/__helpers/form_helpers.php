<?php

////////////////////////////////////////////////////////////////////////////////

function formInput(string $name, string $label, string $type = 'text'): string
{
	return <<<HTML
		<div class="row mb-3">
			<label for="$name" class="col-2 col-form-label">$label</label>
			<div class="col-10">
				<input type="$type" name="$name" id="$name" class="form-control form-control-sm">
			</div>
		</div>
HTML;
}

function formTextarea(string $name, string $label): string
{
	return <<<HTML
		<div class="row mb-3">
			<label for="$name" class="col-2 col-form-label">$label</label>
		<div class="col-10">
			<textarea name="$name" id="$name" rows="3" class="form-control form-control-sm"></textarea>
		</div>
	</div>
HTML;
}

function formSubmit(): string
{
	return <<<HTML
	<div class="row">
        <div class="col-2">
        </div>
        <div class="col-10">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
HTML;
}
