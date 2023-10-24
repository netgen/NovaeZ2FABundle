<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Listener;

use Ibexa\Core\MVC\Symfony\Security\User;
use Netgen\Bundle\Ibexa2FABundle\Core\SiteAccessAwareAuthenticatorResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use function method_exists;

final class OnHttpRequestListener
{
    private TokenStorageInterface $tokenStorage;

    private SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver;

    private RouterInterface $router;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver,
        RouterInterface $router,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->saAuthenticatorResolver = $saAuthenticatorResolver;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $setupUri = $this->router->generate('2fa_setup');

        $isMainRequestMethod = method_exists($event, 'isMainRequest') ? 'isMainRequest' : 'isMasterRequest';

        if (
            !$event->{$isMainRequestMethod}() || !$this->saAuthenticatorResolver->isForceSetup()
            || $request->getRequestUri() === $setupUri
        ) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            if (($user instanceof User) && !$this->saAuthenticatorResolver->checkIfUserSecretOrEmailExists($user)) {
                $response = new RedirectResponse($setupUri);
                $event->setResponse($response);
            }
        }
    }
}
