<?php
namespace AppBundle\Auth;

use AppBundle\Entity\User;

use FOS\RestBundle\Controller\FOSRestController;


class Auth extends FOSRestController
{
    private $user_manager;
    private $factory;

    //Set user_manager by service
    public function __construct($usermanager, $factory){
        $this->user_manager = $usermanager;
        $this->factory = $factory;
    }


    //Checks if user exist in DB
    public function isUser($username, $password){

        $bool = false;

        //Takes User from DB by username
        $user = $this->user_manager->findUserByUsername($username);

        //Checks if it valid USer
        if($user instanceof User){

            $encoder = $this->factory->getEncoder($user);

            //Returns if password match with username
            $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? true : false;
        }

        return  $bool;
    }
}