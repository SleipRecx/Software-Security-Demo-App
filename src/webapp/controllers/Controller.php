<?php

namespace tdt4237\webapp\controllers;

class Controller
{
    protected $app;

    protected $userRepository;
    protected $auth;
    protected $patentRepository;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
        $this->userRepository = $this->app->userRepository;
        $this->patentRepository = $this->app->patentRepository;
        $this->patentRepository = $this->app->patentRepository;
        $this->auth = $this->app->auth;
        $this->hash = $this->app->hash;
    }

  //xss mitigation functions
  public function xssafe($data,$encoding='UTF-8'){
   return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,$encoding);
 }

    protected function render($template, $variables = [])
    {
        if ($this->auth->check()) {
          session_regenerate_id(true);
          $session_id = session_id();
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = $this->auth->isAdmin();
            $variables['loggedInUsername'] = $_SESSION['user'];
        }

        print $this->app->render($template, $variables);
    }
}
