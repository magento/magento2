<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler;

class ConstructorArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $isVariadic;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->name = $configuration[0];
        $this->type = $configuration[1];
        $this->isRequired = $configuration[2];
        $this->defaultValue = $configuration[3];
        $this->isVariadic = $configuration[4];
    }

    /**
     * Returns attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Whether attribute is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * Returns attribute default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns argument is variadic
     *
     * @return bool
     */
    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }
}
