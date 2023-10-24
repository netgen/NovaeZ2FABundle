<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Netgen\Bundle\Ibexa2FABundle\Entity\UserEmailAuth;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;

final class EmailCodeGenerator implements CodeGeneratorInterface
{
    /**
     * @var AuthCodeMailerInterface
     */
    private $mailer;

    /**
     * @var int
     */
    private $digits;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository, AuthCodeMailerInterface $mailer, int $digits)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->digits = $digits;
    }

    public function generateAndSend(TwoFactorInterface $user): void
    {
        $min = 10 ** ($this->digits - 1);
        $max = 10 ** $this->digits - 1;
        $code = $this->generateCode($min, $max);
        /* @var UserEmailAuth $user */
        $user->setEmailAuthCode((string) $code);
        $this->userRepository->updateEmailAuthenticationCode($user->getAPIUser()->getUserId(), (string) $code);
        $this->mailer->sendAuthCode($user);
    }

    public function reSend(TwoFactorInterface $user): void
    {
        $this->mailer->sendAuthCode($user);
    }

    private function generateCode(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}
