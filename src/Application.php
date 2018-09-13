<?php
namespace BookingApp;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;

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
		$this->get('/bookings/create', function () use ($app) {
			return $this['twig']->render('base.html.twig');
		});
	}
}