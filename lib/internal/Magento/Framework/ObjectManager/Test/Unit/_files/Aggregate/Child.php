<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Aggregate;

class Child extends \Magento\Test\Di\Aggregate\AggregateParent
{
    public $secondScalar;

    public $secondOptionalScalar;

    public function __construct(
        \Magento\Test\Di\DiInterface $interface,
        \Magento\Test\Di\DiParent $parent,
        \Magento\Test\Di\Child $child,
        $scalar,
        $secondScalar,
        $optionalScalar = 1,
        $secondOptionalScalar = ''
    ) {
        parent::__construct($interface, $parent, $child, $scalar, $optionalScalar);
        $this->secondScalar = $secondScalar;
        $this->secondOptionalScalar = $secondOptionalScalar;
    }
}
