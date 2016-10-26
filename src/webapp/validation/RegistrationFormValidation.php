<?php

namespace tdt4237\webapp\validation;

use tdt4237\webapp\models\User;

class RegistrationFormValidation
{
    const MIN_USER_LENGTH = 3;

    private $validationErrors = [];

    public function __construct($username, $password, $first_name, $last_name, $phone, $company)
    {
        return $this->validate($username, $password, $first_name, $last_name, $phone, $company);
    }

    public function isGoodToGo()
    {
        return empty($this->validationErrors);
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    private function validate($username, $password, $first_name, $last_name, $phone, $company)
    {
      if ($this->stringTooLong($username, $password, $first_name, $last_name, $phone, $company)){
        $this->validationErrors[] = 'Input field can not have more than 50 characters';
      }

      else{

          if (empty($password)) {
              $this->validationErrors[] = 'Password cannot be empty';
          }

          if (!preg_match('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', $password))
          {
            $this->validationErrors[] = "Password must contain numbers, capital letters, and be atleast 8 characters long";
          }

          if(empty($first_name)) {
              $this->validationErrors[] = "Please write in your first name";
          }

           if(empty($last_name)) {
              $this->validationErrors[] = "Please write in your last name";
          }

          if(empty($phone)) {
              $this->validationErrors[] = "Please write in your post code";
          }

          if (strlen($phone) != 8) {
              $this->validationErrors[] = "Phone number must be exactly eight digits";
          }

          if(strlen($company) > 0 && (!preg_match('/[^0-9]/',$company)))
          {
              $this->validationErrors[] = 'Company can only contain letters';
          }

          if (preg_match('/^[A-Za-z0-9_]+$/', $username) === 0) {
              $this->validationErrors[] = 'Username can only contain letters and numbers';
          }
        }
    }

    public function stringTooLong($input_field1, $input_field2, $input_field3, $input_field4, $input_field5, $input_field6){
      $length1 = strlen($input_field1);
      $length2 = strlen($input_field2);
      $length3 = strlen($input_field3);
      $length4 = strlen($input_field4);
      $length5 = strlen($input_field5);
      $length6 = strlen($input_field6);

      return $length1 > 50 || $length2 > 50 || $length3 > 50 || $length4 > 50 || $length5 > 50 || $length6 > 50;
    }
}
