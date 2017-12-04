<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Argument\ArgumentValueInterface;

/**
 * General interface to use for representing an argument of a field.
 */
interface ArgumentInterface
{
    /**
     * Return argument name
     *
     * @return string
     */
    public function getName();

    /**
     * Return argument value
     *
     * @return ArgumentValueInterface|ArgumentValueInterface[]|int|int[]|string|string[]|float|float[]|bool
     */
    public function getValue();
}
