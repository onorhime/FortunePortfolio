<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig   = $twig;
    }

    /**
     * Sends a welcome email to the specified user.
     *
     * @param User $user
     */
    public function sendEmail(string $recipientEmail, string $subject, string $templatePath, array $context = []): void
    {
        $email = (new TemplatedEmail())
        ->from(new Address('fortune@fortunesportfolio.com', 'Fortune Portfolio'))
        ->to($recipientEmail)
        ->subject($subject)
        ->htmlTemplate($templatePath)
        ->context($context);

    $this->mailer->send($email);

        $this->mailer->send($email);
    }
}
