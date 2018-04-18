<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Describes a value for an enum type.
 */
class EnumValue implements ConfigElementInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param string $value
     * @param string $description
     */
    public function __construct(string $name, string $value, string $description = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->description = $description;
    }

    /**
     * Get the enum value's name/key.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the enum value's value.
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * Get the enum value's description.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
