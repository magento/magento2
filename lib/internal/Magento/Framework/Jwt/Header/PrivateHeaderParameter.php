<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Header;

use Magento\Framework\Jwt\Jwe\JweHeaderParameterInterface;
use Magento\Framework\Jwt\Jws\JwsHeaderParameterInterface;

class PrivateHeaderParameter implements JwsHeaderParameterInterface, JweHeaderParameterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|bool|int|float|string|null
     */
    private $value;

    /**
     * @param string $name
     * @param array|bool|float|int|string|null $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
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
        return self::CLASS_PRIVATE;
    }
}
