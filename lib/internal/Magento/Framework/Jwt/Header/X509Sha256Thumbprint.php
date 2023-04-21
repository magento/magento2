<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Header;

use Magento\Framework\Jwt\Jwe\JweHeaderParameterInterface;
use Magento\Framework\Jwt\Jws\JwsHeaderParameterInterface;

/**
 * "x5t#S256" header.
 */
class X509Sha256Thumbprint implements JwsHeaderParameterInterface, JweHeaderParameterInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $this->base64UrlEncode($value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'x5t#S256';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getClass(): ?int
    {
        return self::CLASS_REGISTERED;
    }

    private function base64UrlEncode(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }
}
