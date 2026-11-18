<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
    private bool $isAutowire = false;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->name = $configuration[0];
        $this->type = $configuration[1];
        $this->isRequired = $configuration[2];
        $this->defaultValue = $configuration[3];
        $this->isAutowire = (bool)($configuration[5] ?? false);
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
     * Whether an optional dependency should be resolved by the container.
     *
     * @return bool
     */
    public function isAutowire(): bool
    {
        return $this->isAutowire;
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
}
