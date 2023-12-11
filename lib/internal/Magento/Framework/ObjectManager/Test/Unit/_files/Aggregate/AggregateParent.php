<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Di\Aggregate;

use Magento\Test\Di\Child;
use Magento\Test\Di\DiInterface;
use Magento\Test\Di\DiParent;

class AggregateParent implements AggregateInterface
{
    public $interface;

    public $parent;

    public $child;

    public $scalar;

    public $optionalScalar;

    /**
     * AggregateParent constructor.
     * @param DiInterface $interface
     * @param DiParent $parent
     * @param Child $child
     * @param $scalar
     * @param int $optionalScalar
     */
    public function __construct(
        DiInterface $interface,
        DiParent $parent,
        Child $child,
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
