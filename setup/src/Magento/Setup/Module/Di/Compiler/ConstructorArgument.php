<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler;

/**
 * Class \Magento\Setup\Module\Di\Compiler\ConstructorArgument
 *
 * @since 2.0.0
 */
class ConstructorArgument
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $name;

    /**
     * @var string
     * @since 2.0.0
     */
    private $type;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $isRequired;

    /**
     * @var mixed
     * @since 2.0.0
     */
    private $defaultValue;

    /**
     * @param array $configuration
     * @since 2.0.0
     */
    public function __construct(array $configuration)
    {
        $this->name = $configuration[0];
        $this->type = $configuration[1];
        $this->isRequired = $configuration[2];
        $this->defaultValue = $configuration[3];
    }

    /**
     * Returns attribute name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns attribute type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Whether attribute is required
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * Returns attribute default value
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
