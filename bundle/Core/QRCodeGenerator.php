<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticator;

final class QRCodeGenerator
{
    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    /**
     * @var TotpAuthenticator
     */
    private $totpAuthenticator;

    public function __construct(GoogleAuthenticator $googleAuthenticator, TotpAuthenticator $totpAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->totpAuthenticator = $totpAuthenticator;
    }

    public function createFromUser($user): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        if ($user instanceof GoogleTwoFactorInterface) {
            $qrContent = $this->googleAuthenticator->getQRContent($user);
        } else {
            $qrContent = $this->totpAuthenticator->getQRContent($user);
        }

        return $writer->writeString($qrContent);
    }
}
