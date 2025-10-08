<?php

namespace OCA\Empleados\AppInfo;

use OCA\Empleados\Activity\ActivityProvider;
use OCA\Empleados\Cron\ActualizarAniversarios;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Helper\MailHelper;

use OCP\Activity\IManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

use OCP\Mail\IMailer;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\BackgroundJob\IJobList;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class Application extends App implements IBootstrap {
	public const APP_ID = 'empleados';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// Registrar MailHelper
		$context->registerService(MailHelper::class, function($c) {
			return new MailHelper(
				$c->query(IMailer::class),
				$c->query(IL10N::class),
				'servicios.torreon@mail.com', // Cambiar por el correo deseado
				$c->query(IURLGenerator::class)
			);
		});
	}

	public function boot(IBootContext $context): void {
		// Registrar actividad
		$context->injectFn(function(IManager $activityManager) {
			$activityManager->registerProvider(ActivityProvider::class);
		});

		// Registrar cron job
		$context->injectFn(function(IJobList $jobList) {
			if (!$jobList->has(ActualizarAniversarios::class, '')) {
				$jobList->add(ActualizarAniversarios::class);
			}
		});
	}
}
