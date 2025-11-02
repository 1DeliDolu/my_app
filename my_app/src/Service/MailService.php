<?php

namespace App\Service;

use App\Entity\Order;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
            ->from(new Address('no-reply@pehlione.shop', 'PehliONE'))
            ->to($to)
            ->subject('Welcome to PehliONE!')
            ->text(
                <<<TEXT
                Hello {$name},

                Welcome to PehliONE! Your account has been successfully created.
                You can now log in and start shopping.

                Thanks,
                PehliONE Team
                TEXT
            )
            ->html(
                <<<HTML
                <h2>Hello {$name},</h2>
                <p>Welcome to <strong>PehliONE</strong>! Your account has been successfully created.</p>
                <p>You can now log in and start shopping 🛍️</p>
                <p>Thanks,<br />PehliONE Team</p>
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

    public function sendOrderConfirmation(Order $order): void
    {
        $user = $order->getUser();
        if (null === $user) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@pehlione.shop', 'PehliONE'))
            ->to($user->getEmail())
            ->subject(sprintf('Order Confirmation #%d', $order->getId()))
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context([
                'order' => $order,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Order confirmation email sent', ['order' => $order->getId()]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Order confirmation email failed', [
                'order' => $order->getId(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
