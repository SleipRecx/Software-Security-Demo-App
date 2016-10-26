<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Patent;
use tdt4237\webapp\models\PatentCollection;

class PatentRepository
{

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function makePatentFromRow(array $row)
    {
        $patent = new Patent($row['patentId'], $row['company'], $row['title'], $row['description'], $row['date'], $row['file']);
        $patent->setPatentId($row['patentId']);
        $patent->setCompany($row['company']);
        $patent->setTitle($row['title']);
        $patent->setDescription($row['description']);
        $patent->setDate($row['date']);
        $patent->setFile($row['file']);

        return $patent;
    }


    public function find($patentId)
    {
       $stmt = $this->pdo->prepare("SELECT * FROM patent WHERE patentId = :value"); $stmt->bindParam(':value', $patentId);
       $stmt->execute();
       $row = $stmt->fetch();

        if($row === false) {
            return false;
        }


        return $this->makePatentFromRow($row);
    }

    public function all()
    {
        $sql   = "SELECT * FROM patent";
        $results = $this->pdo->query($sql);

        if($results === false) {
            return [];
            throw new \Exception('PDO error in patent all()');
        }

        $fetch = $results->fetchAll();
        if(count($fetch) == 0) {
            return false;
        }

        return new PatentCollection(
            array_map([$this, 'makePatentFromRow'], $fetch)
        );
    }

    public function searchFor($searchString){

      if($searchString == "" ){
        return false;
      }
        $string =  '%' . $searchString . '%';
        $stmt = $this->pdo->prepare("SELECT * FROM patent WHERE  title LIKE :string OR company LIKE :string");
        $stmt->bindParam(':string', $string);
        $stmt->execute();
        $fetch = $stmt->fetchAll();

        if(count($fetch) == 0) {
            return false;
        }

        return new PatentCollection(
            array_map([$this, 'makePatentFromRow'], $fetch)
        );
    }



    public function deleteByPatentid($patentId)
    {
       $stmt = $this->pdo->prepare("DELETE FROM patent WHERE patentid=:id");
       $stmt->bindParam(':id', $patentId);
       return $stmt->execute();
    }


    public function save(Patent $patent)
    {
        $title          = $patent->getTitle();
        $company        = $patent->getCompany();
        $description    = $patent->getDescription();
        $date           = $patent->getDate();
        $file           = $patent->getFile();

        if ($patent->getPatentId() === null) {

           $stmt = $this->pdo->prepare("INSERT INTO patent (company, date, title, description, file) VALUES (:company, :date, :title, :description, :file)");
           $stmt->bindParam(':company', $company);
           $stmt->bindParam(':date', $date);
           $stmt->bindParam(':title', $title);
           $stmt->bindParam(':description', $description);
           $stmt->bindParam(':file', $file);

           $stmt->execute();

        }

        return $this->pdo->lastInsertId();
    }
}
