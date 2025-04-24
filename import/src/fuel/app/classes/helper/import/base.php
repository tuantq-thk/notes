<?php

/**
 * Class Helper_Import_Base
 *
 * This class is responsible for importing CSV base.
 * Author: tuantq
 */
abstract class Helper_Import_Base
{
	/**
	 * Get import_csv config
	 *
	 * Reads the import_csv config and returns the defaults.
	 *
	 * @return array
	 */
	protected function get_default_config(): array
	{
		$config = \Config::load('import_csv', true);

		return [
			'delimiter' => $config['csv']['delimiter'] ?? ';',
			'enclosure' => $config['csv']['enclosure'] ?? '"',
			'escape' => $config['csv']['escape'] ?? '\\',
			'from_encoding' => $config['csv']['from_encoding'] ?? 'UTF-8',
			'to_encoding' => $config['csv']['to_encoding'] ?? 'UTF-8'
		];
	}

	/**
	 * Import data from a CSV file.
	 *
	 * @param string $file_path
	 * @param array $header_mappings
	 * @param array $config
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function parse(
		string $file_path,
		array $header_mappings,
		array $config = []
	) {
		$errors = [];
		$data = [];

		$default_config = $this->get_default_config();
		$config = array_merge($default_config, $config);

		$delimiter = $config['delimiter'];
		$enclosure = $config['enclosure'];
		$escape = $config['escape'];
		$from_encoding = $config['from_encoding'];
		$to_encoding = $config['to_encoding'];

		if (!is_readable($file_path)) {
			throw new \Exception("Cannot open file: $file_path");
		}

		if (empty($header_mappings)) {
			throw new \Exception('Headers are not provider');
		}

		$handle = fopen($file_path, 'r');
		if ($handle === false) {
			throw new \Exception("Cannot read file: $file_path");
		}

		try {
			$headers = fgetcsv($handle, 0, $delimiter, $enclosure, $escape);
			if ($headers === false) {
				throw new \Exception("Cannot read header of CSV.");
			}

			if (!empty($headers)) {
				$headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
			}

			$detected_encoding = $from_encoding ?? mb_detect_encoding(implode('', $headers), mb_detect_order(), true);

			$headers = array_map(function ($header) use ($to_encoding, $detected_encoding) {
				return mb_convert_encoding($header, $to_encoding, $detected_encoding);
			}, $headers);

			$header_indexes = $this->map_headers($header_mappings, $headers);
			
			$row_number = 1;

			// Process each row of data in the CSV
			foreach ($this->get_rows($handle, $delimiter, $enclosure, $escape) as $row) {
				$row_number++;

				$row = array_map(function ($col) use ($to_encoding, $detected_encoding) {
					return mb_convert_encoding($col, $to_encoding, $detected_encoding);
				}, $row);

				$row_data = [];

				foreach ($header_mappings as $key => $expected_column) {
					$index = $header_indexes[$key];
					$row_data[$key] = isset($row[$index]) ? trim($row[$index]) : null;
				}

				$validation_errors = $this->validate_row($row_data, $row_number);
				if (!empty($validation_errors)) {
					$errors[$row_number] = $validation_errors;
				} else {
					$data[] = $row_data;
				}
			}
		} finally {
			fclose($handle);
		}

		return ['data' => $data, 'errors' => $errors];
	}

	/**
	 * Maps expected headers to their indexes.
	 *
	 * @param array $header_mappings
	 * @param array $headers
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function map_headers(array $header_mappings, array $headers): array
	{
		$header_indexes = [];

		foreach ($header_mappings as $key => $expected_column) {
			// Find the index of the expected column
			$index = array_search(trim($expected_column), array_map('trim', $headers));

			if ($index === false) {
				throw new \Exception("Column '$expected_column' not found. Available headers: " . implode(', ', $headers));
			}

			$header_indexes[$key] = $index;
		}

		return $header_indexes;
	}

	/**
	 * Get CSV rows as a generator.
	 *
	 * @param resource $handle
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape
	 *
	 * @return \Generator
	 */
	public function get_rows($handle, string $delimiter, string $enclosure, string $escape): \Generator
	{
		while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
			if (!empty(array_filter($row))) {
				yield $row;
			}
		}
	}

	abstract protected function validate_row(array $row, int $row_number): array;

	/**
	 * Formats an error message for a specific row in a CSV file.
	 *
	 * @param int $row_number
	 * @param string $label
	 * @param string $message
	 *
	 * @return string
	 */
	public function format_error_message(int $row_number, string $label, string $message): string
	{
		return "行 {$row_number}「{$label}」{$message}";
	}
}
