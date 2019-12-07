<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Aggregate;

class Child extends \Magento\Test\Di\Aggregate\AggregateParent
{
    public $secondScalar;

    public $secondOptionalScalar;

    /**
     * Child constructor.
     * @param \Magento\Test\Di\DiInterface $interface
     * @param \Magento\Test\Di\DiParent $parent
     * @param \Magento\Test\Di\Child $child
     * @param $scalar
     * @param $secondScalar
     * @param int $optionalScalar
     * @param string $secondOptionalScalar
     */
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
