<div class="g-container">
	<section class="f-pubAppli">
		<h2 class="contact-ttl">Import File</h2>
		<div class="pubAppli-body">

			<?php echo Form::open([
				'name' => 'upload_csv',
				'action' => Uri::create('import_newentry/import/'),
				'method' => 'POST',
				'enctype' => 'multipart/form-data'
			]); ?>

			<input type="file" name="file">
			<button type="submit">Upload</button>

			<?php echo Form::close(); ?>
		</div>
	</section>
</div>
