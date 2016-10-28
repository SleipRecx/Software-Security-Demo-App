<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Phone;
use tdt4237\webapp\models\Email;
use tdt4237\webapp\models\NullUser;
use tdt4237\webapp\models\User;

class UserRepository
{


   const INSERT_QUERY   = "INSERT INTO users(user, pass, first_name, last_name, phone, company, isadmin) VALUES(:user, :pass, :first_name , :last_name , :phone, :company, :admin)";
   const UPDATE_QUERY  = "UPDATE users SET email=:email, first_name=:first_name, last_name=:last_name, isadmin=:admin, phone =:phone , company =:company WHERE id=:id";
   const FIND_BY_NAME = "SELECT * FROM users WHERE user=:user";
   const DELETE_BY_NAME = "DELETE FROM users WHERE user=:user";
   const SELECT_ALL = "SELECT * FROM users";
   const FIND_FULL_NAME  = "SELECT * FROM users WHERE user=:user";

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function makeUserFromRow(array $row)
    {
        $user = new User($row['user'], $row['pass'], $row['first_name'], $row['last_name'], $row['phone'], $row['company']);
        $user->setUserId($row['id']);
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setPhone($row['phone']);
        $user->setCompany($row['company']);
        $user->setIsAdmin($row['isadmin']);

        if (!empty($row['email'])) {
            $user->setEmail(new Email($row['email']));
        }

        if (!empty($row['phone'])) {
            $user->setPhone(new Phone($row['phone']));
        }

        return $user;
    }

    public function getNameByUsername($username)
    {
        $stmt = $this->pdo->prepare(self::FIND_FULL_NAME);
        $stmt->bindParam(':user', $username);

        $stmt->execute();
        $row = $stmt->fetch();

        $name = $row['first_name'] + " " + $row['last_name'];
        return $name;
    }

    public function findByUser($username)
    {
         $stmt = $this->pdo->prepare(self::FIND_BY_NAME);
         $stmt->bindParam(':user', $username);

         $stmt->execute();
         $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }
        try {
          return $this->makeUserFromRow($row);

        } catch (Exception $e) {
            return false;
        }

    }

    public function deleteByUsername($username)
    {
      $stmt = $this->pdo->prepare(self::DELETE_BY_NAME);
      $stmt->bindParam(':user', $username);
      return $stmt->execute();
    }

    public function all()
    {
        $rows = $this->pdo->query(self::SELECT_ALL);

        if ($rows === false) {
            return [];
            throw new \Exception('PDO error in all()');
        }

        return array_map([$this, 'makeUserFromRow'], $rows->fetchAll());
    }

    public function save(User $user)
    {
        if ($user->getUserId() === null) {
            return $this->saveNewUser($user);
        }

        $this->saveExistingUser($user);
    }

    public function saveNewUser(User $user)
    {
      $stmt = $this->pdo->prepare(self::INSERT_QUERY);

      $username = $user->getUsername();
      $pass = $user->getHash();
      $first_name = $user->getFirstName();
      $last_name = $user->getLastName();
      $phone= $user->getPhone();
      $company = $user->getCompany();
      $admin = $user->isAdmin();

      $stmt->bindParam(':user', $username);
      $stmt->bindParam(':pass', $pass);
      $stmt->bindParam(':first_name' ,$first_name);
      $stmt->bindParam(':last_name',$last_name);
      $stmt->bindParam(':phone', $phone);
      $stmt->bindParam(':company', $company);
      $stmt->bindParam(':admin', $admin);
      return $stmt->execute();
    }

    public function saveExistingUser(User $user)
    {
      $stmt = $this->pdo->prepare(self::UPDATE_QUERY);

      $email=  $user->getEmail();
      $first_name = $user->getFirstName();
      $last_name = $user->getLastName();
      $phone= $user->getPhone();
      $company = $user->getCompany();
      $admin = $user->isAdmin();
      $id = $user->getUserId();

      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':id', $id);
      $stmt->bindParam(':first_name' ,$first_name);
      $stmt->bindParam(':last_name',$last_name);
      $stmt->bindParam(':phone', $phone);
      $stmt->bindParam(':company', $company);
      $stmt->bindParam(':admin', $admin);
      return $stmt->execute();
    }

}
