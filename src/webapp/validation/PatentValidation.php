<?php

namespace tdt4237\webapp\validation;

use tdt4237\webapp\models\Patent;

class PatentValidation {

    private $validationErrors = [];

    public function __construct($title, $company, $description) {
        return $this->validate($title, $company, $description);
    }

    public function isGoodToGo()
    {
        return \count($this->validationErrors) ===0;
    }

    public function getValidationErrors()
    {
    return $this->validationErrors;
    }

    public function validate($title, $company, $description)
    {
      if ($this->stringTooLong($title, $company, $description)){
        $this->validationErrors[] = 'Input field can not have more than 50 characters';
      }

      else{
          if ($title == null) {
              $this->validationErrors[] = "Title needed";

          }
          if ($company == null) {

              $this->validationErrors[] = "Company/User needed";
          }

          if ($description == null) {

              $this->validationErrors[] = "Description needed";
          }
      }

        return $this->validationErrors;
    }

    public function stringTooLong($input_field1, $input_field2, $input_field3){
      $length1 = strlen($input_field1);
      $length2 = strlen($input_field2);
      $length3 = strlen($input_field3);

      return $length1 > 50 || $length2 > 50 || $length3 > 50;
    }


}
