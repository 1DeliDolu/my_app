<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class MailService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    )
    {
    }

    public function sendWelcomeEmail(string $to, string $name): void
    {
        $email = (new Email())
            ->from(new Address('no-reply@myshop.com', 'MyShop'))
            ->to($to)
            ->subject('Welcome to MyShop!')
            ->text(
                <<<TEXT
                Hello {$name},

                Welcome to MyShop! Your account has been successfully created.
                You can now log in and start shopping.

                Thanks,
                MyShop Team
                TEXT
            )
            ->html(
                <<<HTML
                <h2>Hello {$name},</h2>
                <p>Welcome to <strong>MyShop</strong>! Your account has been successfully created.</p>
                <p>You can now log in and start shopping 🛍️</p>
                <p>Thanks,<br />MyShop Team</p>
                HTML
            );

        try {
            $this->mailer->send($email);
            $this->logger->info('Welcome email sent', ['to' => $to]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Welcome email failed', [
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
