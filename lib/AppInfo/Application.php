<?php

namespace OCA\Empleados\AppInfo;

use OCA\Empleados\Activity\ActivityProvider;
use OCA\Empleados\Helper\MailHelper;
use OCP\Activity\IManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Mail\IMailer;
use OCP\IL10N;

use OCP\IURLGenerator;

class Application extends App implements IBootstrap {
    public const APP_ID = 'empleados';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        // Registrar el MailHelper
        $context->registerService(MailHelper::class, function($c) {
            $mailer = $c->query(IMailer::class);
            $l10n = $c->query(IL10N::class);
            $urlGenerator = $c->query(IURLGenerator::class);
            $fromAddress = 'servicios.torreon@crowe.mx';
            return new MailHelper($mailer, $l10n, $fromAddress, $urlGenerator);
        });
    }

    public function boot(IBootContext $context): void {
        $context->injectFn(function(IManager $activityManager) {
            $activityManager->registerProvider(ActivityProvider::class);
        });
    }
}
