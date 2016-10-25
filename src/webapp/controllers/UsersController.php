<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Phone;
use tdt4237\webapp\models\Email;
use tdt4237\webapp\models\User;
use tdt4237\webapp\validation\EditUserFormValidation;
use tdt4237\webapp\validation\RegistrationFormValidation;

class UsersController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function show($username)
    {
      $user = $this->userRepository->findByUser($username);
        if ($this->auth->guest()) {
            $this->app->flash("info", "You must be logged in to do that");
            $this->app->redirect("/login");

        } else if ($this->auth->isAdmin() || $user->getUsername() === $this->auth->getUsername()) {

          $this->render('users/showExtended.twig', [
                  'user' => $user,
                  'username' => $username
              ]);
        }

        else{
          $this->app->flash("info", "You can only view information about your own account.");
          $this->app->redirect("/");
        }
    }

    public function newuser()
    {
        if ($this->auth->guest()) {
            return $this->render('users/new.twig', []);
        }

        $username = $this->auth->user()->getUserName();
        $this->app->flash('info', 'You are already logged in as ' . $username);
        $this->app->redirect('/');
    }

    //xss mitigation functions
  public function xssafe($data,$encoding='UTF-8'){
   return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,$encoding);
 }

    public function create()
    {
        $request  = $this->app->request;
        $username = $this->xssafe($request->post('user'));
        $password = $this->xssafe($request->post('pass'));
        $firstName = $this->xssafe($request->post('first_name'));
        $lastName = $this->xssafe($request->post('last_name'));
        $phone = $this->xssafe($request->post('phone'));
        $company = $this->xssafe($request->post('company'));


        $validation = new RegistrationFormValidation($username, $password, $firstName, $lastName, $phone, $company);

        if ($validation->isGoodToGo()) {
            $password = $password;
            $password = $this->hash->make($password);
            $user = new User($username, $password, $firstName, $lastName, $phone, $company);
            $this->userRepository->save($user);

            $this->app->flash('info', 'Thanks for creating a user. Now log in.');
            return $this->app->redirect('/login');
        }

        $errors = join("<br>\n", $validation->getValidationErrors());
        $this->app->flashNow('error', $errors);
        $this->render('users/new.twig', ['username' => $username]);
    }

    public function edit()
    {
        $this->makeSureUserIsAuthenticated();

        $this->render('users/edit.twig', [
            'user' => $this->auth->user()
        ]);
    }

    public function update()
    {
        $this->makeSureUserIsAuthenticated();
        $user = $this->auth->user();

        $request  = $this->app->request;
        $email  = $this->xssafe($request->post('email'));
        $firstName = $this->xssafe($request->post('first_name'));
        $lastName = $this->xssafe($request->post('last_name'));
        $phone = $this->xssafe($request->post('phone'));
        $company = $this->xssafe($request->post('company'));


        $validation = new EditUserFormValidation($email, $phone, $company);

        if ($validation->isGoodToGo()) {
            $user->setEmail(new Email($email));
            $user->setCompany($company);
            $user->setPhone(new Phone($phone));
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $this->userRepository->save($user);

            $this->app->flashNow('info', 'Your profile was successfully saved.');
            return $this->render('users/edit.twig', ['user' => $user]);
        }

        $this->app->flashNow('error', join('<br>', $validation->getValidationErrors()));
        $this->render('users/edit.twig', ['user' => $user]);
    }

    public function destroy($username)
    {
      $user = $this->userRepository->findByUser($username);

        if ($this->auth->isAdmin() && $user->getUsername() !== $this->auth->getUsername()) {
            $this->userRepository->deleteByUsername($username) === 1;
            $this->app->flash('info', "Sucessfully deleted '$username'");
            $this->app->redirect('/admin');
            return;
        }

        $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
        $this->app->redirect('/');
    }

    public function makeSureUserIsAuthenticated()
    {
        if ($this->auth->guest()) {
            $this->app->flash('info', 'You must be logged in to edit your profile.');
            $this->app->redirect('/login');
        }
    }

    public function makeSureUserIsAdmin()
    {
        if ($this->auth->isAdmin()) {
            $this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
        }
    }
}
