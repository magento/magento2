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
 * "crit" header.
 */
class Critical implements JwsHeaderParameterInterface, JweHeaderParameterInterface
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
        if (!$value) {
            throw new \InvalidArgumentException('Critical header cannot be empty');
        }
        $this->value = array_values($value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'crit';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return json_encode($this->value);
    }

    /**
     * @inheritDoc
     */
    public function getClass(): ?int
    {
        return self::CLASS_REGISTERED;
    }
}
