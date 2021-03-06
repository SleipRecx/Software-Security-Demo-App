<?php

namespace tdt4237\webapp;

use Symfony\Component\Config\Definition\Exception\Exception;

class Hash{

  // blowfish
    private static $algo = '$2a';
    // cost parameter
    private static $cost = '$14';

    public function __construct(){}

      // mainly for internal use
      public static function unique_salt() {
        return substr(sha1(mt_rand()),0,22);
      }

    // this will be used to generate a hash
    public static function make($password) {

        return crypt($password,
                    self::$algo .
                    self::$cost .
                    '$' . self::unique_salt());
    }

  // this will be used to compare a password against a hash
  public function check($password, $hash) {
      $full_salt = substr($hash, 0, 29);
      $new_hash = crypt($password, $full_salt);
      return ($hash == $new_hash);
  }

}
