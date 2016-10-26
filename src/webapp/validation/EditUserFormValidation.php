<?php

namespace tdt4237\webapp\validation;

class EditUserFormValidation
{
    private $validationErrors = [];


    public function __construct($first_name, $last_name, $email, $phone, $company){
      $this->validate($first_name, $last_name, $email, $phone, $company);
    }

    public function isGoodToGo()
    {
        return \count($this->validationErrors) === 0;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    private function validate($first_name, $last_name, $email, $phone, $company)
    {
      if ($this->stringTooLong($first_name, $last_name, $email, $phone, $company)){
        $this->validationErrors[] = 'Input field can not have more than 50 characters';
      }

      else{

          if(empty($first_name)) {
              $this->validationErrors[] = "Please write in your first name";
          }

           if(empty($last_name)) {
              $this->validationErrors[] = "Please write in your last name";
          }

          if (strlen($phone) != 8) {
              $this->validationErrors[] = "Phone number must be exactly eight digits";
          }

          if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $this->validationErrors[] = "Invalid email format on email";
          }

          if(strlen($company) > 0 && (!preg_match('/[^0-9]/',$company)))
          {
              $this->validationErrors[] = 'Company can only contain letters';
          }
        }
      }

        public function stringTooLong($input_field1, $input_field2, $input_field3, $input_field4, $input_field5){
          $length1 = strlen($input_field1);
          $length2 = strlen($input_field2);
          $length3 = strlen($input_field3);
          $length4 = strlen($input_field4);
          $length5 = strlen($input_field5);

          return $length1 > 50 || $length2 > 50 || $length3 > 50 || $length4 > 50 || $length5 > 50;
        }
}
