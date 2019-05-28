<?php

namespace app;

class Validator
{
    public $errors = [];

    public function validate($data) {
        foreach ($data as $name => $val) {
            if(empty($val)) {
                $this->errors[] = [
                    $name => 'This field is required'
                ];
            }
        }

        return $this->errors;
    }
 }