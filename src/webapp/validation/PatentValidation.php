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
        if ($title == null) {
            $this->validationErrors[] = "Title needed";

        }
        if ($company == null) {

            $this->validationErrors[] = "Company/User needed";
        }

        if ($description == null) {

            $this->validationErrors[] = "Description needed";
        }

        return $this->validationErrors;
    }


}
