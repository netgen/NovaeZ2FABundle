<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var Address
     */
    private $senderAddress;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        ?string $senderName,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->senderAddress = new Address($senderEmail, $senderName ?? '');
        $this->translator = $translator;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from($this->senderAddress)
            ->subject($this->translator->trans('email_subject', [], 'novaez2fa'))
            ->text($user->getEmailAuthCode());
        $this->mailer->send($message);
    }
}
