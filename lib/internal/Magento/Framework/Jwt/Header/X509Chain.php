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
 * "x5c" header.
 */
class X509Chain implements JwsHeaderParameterInterface, JweHeaderParameterInterface
{
    /**
     * @var string[]
     */
    private $value;

    /**
     * @param string[] $value
     */
    public function __construct(array $value)
    {
        if (count($value) < 1) {
            throw new \InvalidArgumentException('X.509 Certificate chain must contain at least 1 key');
        }
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'x5c';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return json_encode(array_map('base64_encode', $this->value));
    }

    /**
     * @inheritDoc
     */
    public function getClass(): ?int
    {
        return self::CLASS_REGISTERED;
    }
}
