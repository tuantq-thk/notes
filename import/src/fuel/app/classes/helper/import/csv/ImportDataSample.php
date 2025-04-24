<?php
namespace Helper\Import\Csv;

use Fuel\Core\Validation;
use Helper\Import\Csv\Base;

class ImportDataSample extends Base 
{
    protected $toEncoding = 'UTF-8';
    protected $fromEncoding = 'SJIS-win';

    public function importData($file, $config = [], $options = []) {
        // print_r($file);
        // die();
        $data = $this->getDataImport($file, $config, $options);
        return $data;
    }

    protected function validateRow($row, $rowNumber): array {

        $validation = Validation::forge('validate_row_'. $rowNumber);
    
        $validation->add('name', 'Tên sản phẩm')
        ->add_rule('required')
        ->add_rule('min_length', 3);

        if (!$validation->run($row)) {
            $errors = [];
            foreach ($validation->error() as $field => $error) {
                $errors[$field] = $error->get_message(); 
            }
            return $errors;
        }
    
        return [];
    }
}