<?php
namespace BookingApp;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class Application extends SilexApplication
{
	
	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->configureServices();
		$this->createDBTables();
		$this->configureControllers();


	}

	private function configureServices(){
		$this['debug'] = true;

		//Twigi seadistamine
		$this->register(new TwigServiceProvider(), [
			'twig.path' => __DIR__.'/../views',
		]);

		//Andmebaasi konfiguratsioon
		$this->register(new DoctrineServiceProvider(), [
			'db.options' => [
				'driver' => 'pdo_sqlite',
				'path' => __DIR__.'/../database/app.db',
			],
		]);

		//Form
		$this->register(new FormServiceProvider());
		$this->register(new LocaleServiceProvider());
		$this->register(new TranslationServiceProvider(), [
			'translator.domains' => [],
		]);
	}

	private function createDBTables(){
		//Tehakse tabel 'bookings', kui seda veel olemas ei ole.
		if (!$this['db']->getSchemaManager()->tablesExist('bookings')){
			$this['db']->executeQuery("CREATE TABLE bookings (
				id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				firstName VARCHAR(40) NOT NULL,
				lastName VARCHAR(40) NOT NULL,
				phone VARCHAR(10) NOT NULL,
				email VARCHAR(20) DEFAULT NULL,
				birthday DATE NOT NULL,
				startDate DATE NOT NULL,
				endDate DATE NOT NULL,
				arrivalTime TIME DEFAULT NULL,
				additionalInformation TEXT,
				nrOfPeople INT NOT NULL,
				payingMethod VARCHAR(10) NOT NULL
				);");
		}
	}

	private function configureControllers(){
		//Routes
		$this->get('/bookings/create', function () {
			$form = $this['form.factory']->createBuilder(FormType::class)
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

            return $this['twig']->render('form.html.twig', [
            	'form' => $form->createView(),
            ]);
		});
	}
}