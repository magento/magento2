<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Argument\ArgumentValueInterface;

/**
 * Simple Implementation for interface ArgumentInterface, representing an argument of a field.
 */
class Argument implements ArgumentInterface
{
    /** @var string */
    protected $name;

    /** @var ArgumentValueInterface|ArgumentValueInterface[]|int|int[]|string|string[]|float|float[]|bool */
    protected $value;

    /**
     * @param string $name
     * @param bool|float|int|ArgumentValueInterface|string $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }
}
