<?php

namespace BookingApp\Controllers;

use Doctrine\DBAL\Connection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class CreateBookingController {

	public function __construct(FormFactory $formFactory, \Twig_Environment $twigEnv, Connection $dbConnection) {
		$this->formFactory = $formFactory;
		$this->twigEnv = $twigEnv;
		$this->dbConnection = $dbConnection;
	}

	public function __invoke(Request $request) {
		$form = $this->formFactory->createBuilder(FormType::class)
                ->add('firstName', TextType::class, ['required' => true])
                ->add('lastName', TextType::class, ['required' => true])
                ->add('phone', TextType::class, ['required' => true])
                ->add('email', TextType::class, ['required' => false])
                ->add('birthday', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('startDate', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('endDate', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('arrivalTime', TimeType::class, ['required' => true])
                ->add('nrOfPeople', IntegerType::class, ['required' => true])
                ->add('payingMethod', ChoiceType::class, [
                    'choices' => [
                        'cash' => 'cash',
                        'transfer' => 'transfer',
                    ],
                    'required' => true,
                ])
                ->add('additionalInformation', TextareaType::class, [
                    'required' => false
                ])
                ->add('submit', SubmitType::class, ['label' => 'Send booking'])
                ->getForm()
            ;

            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $st = $this->dbConnection->executeQuery("INSERT INTO bookings (firstName, lastName, phone, email, birthday, startDate, endDate, arrivalTime, nrOfPeople, payingMethod, additionalInformation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $data['firstName'],
                $data['lastName'],
                $data['phone'],
                $data['email'],
                $data['birthday']->format('Y-m-d H:i:s'),
                $data['startDate']->format('Y-m-d H:i:s'),
                $data['endDate']->format('Y-m-d H:i:s'),
                $data['arrivalTime']->format('H:i:s'),
                $data['nrOfPeople'],
                $data['payingMethod'],
                $data['additionalInformation']
            ]);

            return new RedirectResponse($request->getUri());
        }

        return $this->twigEnv->render('form.html.twig', ['form' => $form->createView()]);

	}
}