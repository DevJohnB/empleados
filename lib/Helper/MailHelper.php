<?php

namespace OCA\Empleados\Helper;

use OCP\Mail\IMailer;
use OCP\IL10N;
use OCP\Mail\IEMailTemplate;

// added
use OCP\IURLGenerator;

class MailHelper {

    private IMailer $mailer;
    private IL10N $l10n;
    private string $fromAddress;
    private IURLGenerator $urlGenerator;

    public function __construct(IMailer $mailer, IL10N $l10n, string $fromAddress, IURLGenerator $urlGenerator) {
        $this->mailer = $mailer;
        $this->l10n = $l10n;
        $this->fromAddress = $fromAddress;
        $this->urlGenerator = $urlGenerator;
    }

    public function enviarCorreo(string $to, string $subject, array $contenidoCuerpo): void {
        $message = $this->mailer->createMessage();
        $message->setFrom([$this->fromAddress => 'NUBE GOSSLER - Sistema de Empleados']);
        $message->setTo([$to]);
        $message->setSubject($subject);

        // 🟢 Usar plantilla oficial Nextcloud
        /** @var IEMailTemplate $template */
        $template = $this->mailer->createEMailTemplate('empleados');

        // Agregar encabezado
        $template->addHeading($subject);

        // adding personalized logo
        // Agregar contenido (líneas del cuerpo)
        foreach ($contenidoCuerpo as $linea) {
            $template->addBodyText($linea);
        }

        $urlModulo = $this->urlGenerator->getAbsoluteURL('/apps/empleados/');
        $template->addBodyButton('Ir al módulo de empleados', $urlModulo);


        // Renderizar como HTML y texto plano
        $message->setHtmlBody($template->renderHtml());
        $message->setPlainBody($template->renderText());

        $this->mailer->send($message);
    }
}
