<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

final class AuthCodeMailer implements AuthCodeMailerInterface
{
    private MailerInterface $mailer;

    private Address $senderAddress;

    private TranslatorInterface $translator;

    private TwigEnvironment $twig;

    private SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        ?string $senderName,
        TranslatorInterface $translator,
        TwigEnvironment $twig,
        SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver,
    ) {
        $this->mailer = $mailer;
        $this->senderAddress = new Address($senderEmail, $senderName ?? '');
        $this->translator = $translator;
        $this->twig = $twig;
        $this->saAuthenticatorResolver = $saAuthenticatorResolver;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from($this->senderAddress)
            ->subject($this->translator->trans('email_subject', [], 'netgen_ibexa2fa'))
            ->html(
                $this->twig->render(
                    $this->saAuthenticatorResolver->getEmailTemplate(),
                    ['code' => $user->getEmailAuthCode()],
                ),
            );

        $this->mailer->send($message);
    }
}
