<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Class representing 'enum' GraphQL config element.
 */
class Enum implements ConfigElementInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $values;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param EnumValue[] $values
     * @param string $description
     */
    public function __construct(
        string $name,
        array $values,
        string $description
    ) {
        $this->name = $name;
        $this->values = $values;
        $this->description = $description;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get an array of all possible values for the Enum.
     *
     * @return EnumValue[]
     */
    public function getValues() : array
    {
        return $this->values;
    }

    /**
     * Return human-readable description of Enum.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
