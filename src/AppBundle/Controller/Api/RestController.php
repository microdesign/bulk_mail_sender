<?php
namespace AppBundle\Controller\Api;

use AppBundle\Entity\Email;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Symfony\Component\Validator\Constraints as Assert;

class RestController extends FOSRestController
{

    public function getListAction(Request $request){

        $statusCode = 404;

        //Set username and pass from Request
        $username = $request->headers->get('php-auth-user');
        $password = $request->headers->get('php-auth-pw');

        //Authenticate user creds by service
        $auth = $this->get('app.auth');
        $isUser = $auth->isUser($username, $password);

        //Checks if validation passed
        if($isUser){
            $statusCode = 200;
            $emails = $this->getDoctrine()
                ->getRepository('AppBundle:Email')
                ->findAll();
        }else{
            $statusCode = 401;
            $emails = 'Not Authonticated';

        }

        $view = $this->view($emails, $statusCode);

        return $this->handleView($view);




//        $user_manager = $this->get('fos_user.user_manager');
//        var_dump(  $user_manager->loadUserByUsername($username) ); exit;


        ////////////////////
//
//        $user_manager = $this->get('fos_user.user_manager');
//        $factory = $this->get('security.encoder_factory');
//
//        var_dump($user_manager->loadUserByUsername($username)); exit;
//
//       $user = $user_manager->loadUserByUsername($username);
//
//        $encoder = $factory->getEncoder($user);
//
//        $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? "true" : "false";
//
//        var_dump( $bool);
//        exit;


        ///////////////////

//        $user = $userManager->createUser();
//
//        $repository = $this->getDoctrine()
//            ->getRepository('AppBundle:User');
//
//        // createQueryBuilder automatically selects FROM AppBundle:Product
//        // and aliases it to "p"
//        $query = $repository->createQueryBuilder('p')
//            ->where('p.username = :username')
//            ->setParameter('username', $request->headers->get('php-auth-user'))
//            ->andWhere('p.password = :password')
//            ->setParameter('password', $request->headers->get('php-auth-pw'))
//            ->getQuery();
//
//        $product = $query->setMaxResults(1)->getOneOrNullResult();
//        // to get just one result:
//        // $product = $query->setMaxResults(1)->getOneOrNullResult();
//
//
//
//        var_dump(count($product));
//        var_dump();
//        var_dump();
//        exit;
//
//        return $this->handleView($view);

    }

    public function postEmailAction(Request $request){

        $statusCode = 404;

        //Set username and pass from Request
        $username = $request->headers->get('php-auth-user');
        $password = $request->headers->get('php-auth-pw');

        //Authenticate user creds by service
        $auth = $this->get('app.auth');
        $isUser = $auth->isUser($username, $password);

        //Checks if validation passed
        if($isUser){

            $email_data = json_decode($request->getContent());
            $errorsString = [];

            //Loop for each email
            for ($i = 0; $i < count($email_data); $i++){
                $errors = [];

                $email = new Email();
                $email->setSender( $email_data[$i]->from );
                $email->setReceiver($email_data[$i]->to);
                $email->setSubject($email_data[$i]->subject);
                $email->setMessage($email_data[$i]->message);

                $validator = $this->get('validator');
                $errors = $validator->validate($email);

                $em = $this->getDoctrine()->getManager();

                //If email validation passed
                if (count($errors) == 0) {

                    $message = \Swift_Message::newInstance()
                        ->setSubject($email_data[$i]->subject)
                        ->setFrom($email_data[$i]->from)
                        ->setTo($email_data[$i]->to)
                        ->setBody($email_data[$i]->message);

                    $email->setStatus('Sended');


                    // tells Doctrine you want to (eventually) save the Email (no queries yet)
                    $em->persist($email);

                    try{
                        $this->get('mailer')->send($message);

                        // actually executes the queries
                        $em->flush();

                    } catch(Exception $e){

                        //Response

                        var_dump($e->getMessage());exit;
                    }

                } else {
                    $email->setStatus('Not send');

                    $em->persist($email);
                    $em->flush();

                    $errorsString[] = ['error' => $errors, 'key' => $i ];
                }

            }//End for LOOP

            if(count($errorsString) > 0){

                $statusCode = 400;
                $data = $errorsString;
            } else {
                $statusCode = 200;
                $data = 'Emails are sended';
            }

        } else{
                $statusCode = 401;
                $data = 'Not Authonticated';

            }


        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }
}