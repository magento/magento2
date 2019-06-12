<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Aggregate;

class AggregateParent implements \Magento\Test\Di\Aggregate\AggregateInterface
{
    public $interface;

    public $parent;

    public $child;

    public $scalar;

    public $optionalScalar;

    /**
     * AggregateParent constructor.
<<<<<<< HEAD
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param \Magento\Test\Di\DiInterface $interface
     * @param \Magento\Test\Di\DiParent $parent
     * @param \Magento\Test\Di\Child $child
     * @param $scalar
     * @param int $optionalScalar
     */
    public function __construct(
        \Magento\Test\Di\DiInterface $interface,
        \Magento\Test\Di\DiParent $parent,
        \Magento\Test\Di\Child $child,
        $scalar,
        $optionalScalar = 1
    ) {
        $this->interface = $interface;
        $this->parent = $parent;
        $this->child = $child;
        $this->scalar = $scalar;
        $this->optionalScalar = $optionalScalar;
    }
}
