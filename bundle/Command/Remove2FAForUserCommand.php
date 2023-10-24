<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Command;

use Ibexa\Core\MVC\Symfony\Security\User;
use Netgen\Bundle\Ibexa2FABundle\Core\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class Remove2FAForUserCommand extends Command
{
    private UserProviderInterface $userProvider;

    private UserRepository $userRepository;

    public function setAuthenticators(UserProviderInterface $userProvider, UserRepository $userRepository): self
    {
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('nova:2fa:remove-secret-key')
            ->setDescription('Removes the 2FA secret key for the specified user')
            ->addArgument('user_login', InputArgument::REQUIRED, 'User Login');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var User $user */
        $user = $this->userProvider->loadUserByUsername($input->getArgument('user_login'));

        $this->userRepository->deleteUserAuthData($user->getAPIUser()->id);

        $io->success('Done.');

        return 0;
    }
}
