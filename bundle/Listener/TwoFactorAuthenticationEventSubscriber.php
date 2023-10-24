<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Listener;

use Netgen\Bundle\Ibexa2FABundle\Core\SiteAccessAwareAuthenticatorResolver;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TwoFactorAuthenticationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SiteAccessAwareAuthenticatorResolver
     */
    private $saAwareAuthenticatorResolver;

    public function __construct(SiteAccessAwareAuthenticatorResolver $saAwareAuthenticatorResolver)
    {
        $this->saAwareAuthenticatorResolver = $saAwareAuthenticatorResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TwoFactorAuthenticationEvents::FORM => ['onRenderAuthenticationForm', -200],
        ];
    }

    public function onRenderAuthenticationForm(TwoFactorAuthenticationEvent $event): void
    {
        $event->getToken()->setAttribute('method', $this->saAwareAuthenticatorResolver->getMethod());
    }
}
