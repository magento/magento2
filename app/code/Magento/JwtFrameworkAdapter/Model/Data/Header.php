<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model\Data;

use Magento\Framework\Jwt\Jwe\JweHeaderParameterInterface;
use Magento\Framework\Jwt\Jws\JwsHeaderParameterInterface;

class Header implements JwsHeaderParameterInterface, JweHeaderParameterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int|null
     */
    private $class;

    /**
     * Header constructor.
     * @param string $name
     * @param mixed $value
     * @param int|null $class
     */
    public function __construct(string $name, $value, ?int $class)
    {
        $this->name = $name;
        $this->value = $value;
        $this->class = $class;
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
        return $this->class;
    }
}
