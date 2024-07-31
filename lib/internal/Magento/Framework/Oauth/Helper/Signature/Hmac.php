<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper\Signature;

use Laminas\Uri;
use Laminas\OAuth\Signature\Hmac as HmacSignature;
use Magento\Framework\Oauth\Helper\Uri\Http;

class Hmac extends HmacSignature
{
    /**
     * @inheritDoc
     */
    public function normaliseBaseSignatureUrl($url): string
    {
        $registeredHttpScheme = Uri\UriFactory::getRegisteredSchemeClass('http');
        $registeredHttpsScheme = Uri\UriFactory::getRegisteredSchemeClass('https');
        Uri\UriFactory::registerScheme('http', Http::class);
        Uri\UriFactory::registerScheme('https', Http::class);
        $uri = Uri\UriFactory::factory($url);
        Uri\UriFactory::registerScheme('http', $registeredHttpScheme);
        Uri\UriFactory::registerScheme('https', $registeredHttpsScheme);
        $uri->normalize();
        if ($uri->getScheme() == 'http' && $uri->getPort() == '80') {
            $uri->setPort('');
        } elseif ($uri->getScheme() == 'https' && $uri->getPort() == '443') {
            $uri->setPort('');
        } elseif (! in_array($uri->getScheme(), ['http', 'https'])) {
            throw new \InvalidArgumentException('Invalid URL provided; must be an HTTP or HTTPS scheme');
        }
        $uri->setQuery('');
        $uri->setFragment('');
        return $uri->toString();
    }
}
