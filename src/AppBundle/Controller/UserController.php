<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request) {

        if (is_object($this->getUser())) {
            return $this->redirect('dashboard');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:Default:login.html.twig', array(
                    'error' => $error,
                    'last_user' => $lastUsername
        ));
    }

    public function registerAction(Request $request) {

        if (is_object($this->getUser())) {
            return $this->redirect('dashboard');
        }

        $user = new \BackendBundle\Entity\Users();

        $form = $this->createForm(\AppBundle\Form\UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                //Checar que el usuario no exista en la base de datos
//                $user_login = $em->getRepository('BackendBundle:Users')->findBy([
//                    'password' => $form->get('password')->getData(),
//                    'email' => $form->get('email')->getData()
//                ]);

                $factory = $this->get("security.encoder_factory");
                $encoder = $factory->getEncoder($user);

                $password = $encoder->encodePassword($form->get('password')->getData(), $user->getSalt());

                $user->setPassword($password);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime);

                $em->persist($user);
                $em->flush();
            }
        }

        return $this->render('AppBundle:Default:register.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function dashboardAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BackendBundle:Users');
        $users = $repo->findAll();
        $user = $repo->find($this->getUser()->getId());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $users, $request->query->getInt('page', 1), 5
        );



        return $this->render('AppBundle:User:dashboard.html.twig', array(
                    'users' => $pagination,
                    'user' => $user
        ));
    }

}
