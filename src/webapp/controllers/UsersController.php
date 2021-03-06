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

      if(!$user){
        $this->app->flash("error", "Invalid username!");
        $this->app->redirect("/");
      }

        if ($this->auth->guest()) {
            $this->app->flash("info", "You must be logged in to view this page.");
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
        $this->app->flash('error', 'You are already logged in as ' . $username);
        $this->app->redirect('/');
    }

    public function create()
    {
        $request  = $this->app->request;
        $username = $this->xssafe($request->post('user'));
        $password = $this->xssafe($request->post('pass'));
        $first_name = $this->xssafe($request->post('first_name'));
        $last_name = $this->xssafe($request->post('last_name'));
        $phone = $this->xssafe($request->post('phone'));
        $company = $this->xssafe($request->post('company'));


        $validation = new RegistrationFormValidation($username, $password, $first_name, $last_name, $phone, $company);

        if ($validation->isGoodToGo()) {
            $password = $password;
            $password = $this->hash->make($password);
            $user = new User($username, $password, $first_name, $last_name, $phone, $company);
            $this->userRepository->save($user);

            $this->app->flash('info', 'Thanks for creating a user. Now log in!');
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
        $first_name = $this->xssafe($request->post('first_name'));
        $last_name = $this->xssafe($request->post('last_name'));
        $phone = $this->xssafe($request->post('phone'));
        $company = $this->xssafe($request->post('company'));


        $validation = new EditUserFormValidation($first_name, $last_name, $email, $phone, $company);

        if ($validation->isGoodToGo()) {
            $user->setFirstName($first_name);
            $user->setLastName($last_name);
            $user->setEmail(new Email($email));
            $user->setPhone(new Phone($phone));
            $user->setCompany($company);
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

        $this->app->flash('error', "Unable to delete user '$username'.");
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
            $this->app->flash('info', "You must be an administrator to view this page.");
            $this->app->redirect('/');
        }
    }
}
