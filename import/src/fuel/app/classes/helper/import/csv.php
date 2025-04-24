<?php

/**
 * Class Helper_Import_Csv
 *
 * This class is responsible for importing CSV files and validating the data within.
 * Author: tuantq
 */
class Helper_Import_Csv extends Helper_Import_Base
{

	protected function validate_row(array $row, int $row_number): array
	{
		$validation = \Validation::instance('validate_row' . $row_number) ?: \Validation::forge('validate_row' . $row_number);

		$requiredFields = [
			'password',
			'password_confirm',
			'prefecture',
			'mainarea',
			'business_id',
			'enable_place',
			'staff_name',
			'staff_email',
			'shop_phonetic',
			'tel',
			'working_time',
			'most_cheap_price',
			'credit',
			'pc_url',
			'sp_url'
		];

		foreach ($requiredFields as $field) {
			$validation->add($field, ucfirst($field))->add_rule('required');
		}

		$validation->add('shop_id', 'ログインID')
			->add_rule('required')
			->add_rule('min_length', 4)
			->add_rule('max_length', 20)
			->set_error_message('required', '※入力されていません');
			// ->set_error_message('min_length', 'min 100');

		$validation->add('shop_name', 'ません')
			->add_rule('required')
			// ->add_rule('min_length', 10)
			->set_error_message('required', '※入力されていません');
			// ->set_error_message('min_length', 'min length 12');

		if (!$validation->run($row)) {
			$errors = [];
			foreach ($validation->error() as $field => $error) {
				$label = $validation->field($field)->label ?: $field;
				$message = method_exists($error, 'get_message') ? $error->get_message() : $error->error();
				$errors[] = $this->format_error_message($row_number, $label, $message);
			}
			return $errors;
		}

		return [];
	}
}
