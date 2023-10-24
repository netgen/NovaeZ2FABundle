<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Listener;

use Ibexa\Contracts\Core\Repository\Events\User\DeleteUserEvent;
use Netgen\Bundle\Ibexa2FABundle\Core\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserEventSubscriber implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeleteUserEvent::class => 'onDeleteUser',
        ];
    }

    public function onDeleteUser(DeleteUserEvent $event): void
    {
        $this->userRepository->deleteUserAuthData($event->getUser()->id);
    }
}
