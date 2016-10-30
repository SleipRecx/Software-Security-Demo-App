<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Patent;
use tdt4237\webapp\controllers\UserController;
use tdt4237\webapp\validation\PatentValidation;
use tdt4237\webapp\Auth;

class PatentsController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {

        $patent = $this->patentRepository->all();
        if($patent != null)
        {
            $patent->sortByDate();
        }
        $users = $this->userRepository->all();
        $this->render('patents/index.twig', ['patent' => $patent, 'users' => $users]);
    }

    public function show($patentId)
    {

      if ($this->auth->guest()){
        $this->app->flash('info', "You need to be logged in to view more information.");
        $this->app->redirect('/patents');
        return;
      }

        $patent = $this->patentRepository->find($patentId);
        $username = $_SESSION['user'];
        $user = $this->userRepository->findByUser($username);
        $request = $this->app->request;
        $message = $this->xssafe($request->get('msg'));
        $variables = [];

        if($message) {
            $variables['msg'] = $message;

        }

        $this->render('patents/show.twig', [
            'patent' => $patent,
            'user' => $user,
            'flash' => $variables
        ]);

    }

    public function search(){
      if ($this->auth->guest()){
        $this->app->flash('info', "You must be logged in to view this page.");
        $this->app->redirect('/');
      }
      $this->render('patents/search.twig');
    }

    public function searching(){
      if ($this->auth->guest()){
        $this->app->flash('info', "You must be logged in to view this page.");
        $this->app->redirect('/');
      }
      $request  = $this->app->request;
      $string   = $this->xssafe($request->post('search'));
      $patents = $this->patentRepository->searchFor($string);

      if($patents != null)
      {
          $patents->sortByDate();
      }
      $users = $this->userRepository->all();

      $this->render('patents/search.twig', [
        'showtable' => true,
        'patent' => $patents,
        'users' => $users]);
    }


    public function newpatent()
    {

        if ($this->auth->check()) {
            $username = $_SESSION['user'];
            $this->render('patents/new.twig', ['username' => $username]);
        } else {

            $this->app->flash('info', "You need to be logged in to register a patent");
            $this->app->redirect("/");
        }

    }

    public function create()
    {
        if ($this->auth->guest()) {
            $this->app->flash("info", "You need to be logged in to register a patent");
            $this->app->redirect("/login");
        } else {
            $request     = $this->app->request;
            $title       = $this->xssafe($request->post('title'));
            $description = $this->xssafe($request->post('description'));
            $company     = $this->xssafe($request->post('company'));
            $date        = date("dmY");
            //TODO make this upload safe
            $file = $this -> startUpload();



            $validation = new PatentValidation($title, $description, $company);
            if ($validation->isGoodToGo()) {
                $patent = new Patent($company, $title, $description, $date, $file);
                $patent->setCompany($company);
                $patent->setTitle($title);
                $patent->setDescription($description);
                $patent->setDate($date);
                $patent->setFile($file);
                $savedPatent = $this->patentRepository->save($patent);
                $this->app->redirect('/patents/' . $savedPatent . '?msg="Patent succesfully registered');
            }
        }

            $this->app->flashNow('error', join('<br>', $validation->getValidationErrors()));
            $this->app->render('patents/new.twig');
    }

    public function startUpload()
    {
        if(isset($_POST['submit']))
        {

            //Check if the file size is under 10 MB (filesize in bytes)
            if($_FILES['uploaded']['size'] > 10000000) {
              throw new RuntimeException("File too big!");
            }

            //check mime type
            $allowed =  array('png' ,'jpg');
            $filename = $_FILES['uploaded']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!in_array($ext,$allowed) ) {
              $this->app->flash('error', "Wrong filetype. (Only png and jpg allowed).");
              $this->app->redirect('/patents/new');
            }
            //check that filename isnt too long
            else if (strlen($_FILES['uploaded']['name']) > 32 ) {
              throw new RuntimeException("Filename too long.");
            }
            else {

              $target_dir =  getcwd()."/web/uploads/";
              $targetFile = $target_dir . basename($_FILES['uploaded']['name']);

              if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $targetFile))
              {
                  return $targetFile;
              }
            }
        }
    }

    public function destroy($patentId)
    {
        if (!$this->auth->isAdmin()) {
              $this->app->flash('info', "You must be an administrator to view this page.");
              $this->app->redirect('/patents');
              return;
            }

        else{
            $this->patentRepository->deleteByPatentid($patentId) === 1;
              $this->app->flash('info', "Sucessfully deleted '$patentId'");
              $this->app->redirect('/admin');
              return;
            }

        $this->app->flash('error', "Unable to delete user '$username'.");
        $this->app->redirect('/admin');
    }
}
