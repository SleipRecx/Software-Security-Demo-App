<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Patent;
use tdt4237\webapp\controllers\UserController;
use tdt4237\webapp\validation\PatentValidation;

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
        $patent = $this->patentRepository->find($patentId);
        $username = $_SESSION['user'];
        $user = $this->userRepository->findByUser($username);
        $request = $this->app->request;
        $message = $request->get('msg');
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

    public function newpatent()
    {

        if ($this->auth->check()) {
            $username = $_SESSION['user'];
            $this->render('patents/new.twig', ['username' => $username]);
        } else {

            $this->app->flash('error', "You need to be logged in to register a patent");
            $this->app->redirect("/");
        }

    }

    public function create()
    {
        if ($this->auth->guest()) {
            $this->app->flash("info", "You must be logged on to register a patent");
            $this->app->redirect("/login");
        } else {
            $request     = $this->app->request;
            $title       = $request->post('title');
            $description = $request->post('description');
            $company     = $request->post('company');
            $date        = date("dmY");
            $file = $this -> startUpload();

            $validation = new PatentValidation($title, $description);
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
            $target_dir =  getcwd()."/web/uploads/";
            $targetFile = $target_dir . basename($_FILES['uploaded']['name']);
            if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $targetFile))
            {
                return $targetFile;
            }
        }
    }

    public function destroy($patentId)
    {
        if ($this->patentRepository->deleteByPatentid($patentId) === 1) {
            $this->app->flash('info', "Sucessfully deleted '$patentId'");
            $this->app->redirect('/admin');
            return;
        }

        $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
        $this->app->redirect('/admin');
    }
}
