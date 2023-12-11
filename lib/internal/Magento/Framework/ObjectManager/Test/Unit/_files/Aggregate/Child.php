<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Di\Aggregate;

use Magento\Test\Di\DiInterface;
use Magento\Test\Di\DiParent;

class Child extends AggregateParent
{
    public $secondScalar;

    public $secondOptionalScalar;

    /**
     * Child constructor.
     * @param DiInterface $interface
     * @param DiParent $parent
     * @param \Magento\Test\Di\Child $child
     * @param $scalar
     * @param $secondScalar
     * @param int $optionalScalar
     * @param string $secondOptionalScalar
     */
    public function __construct(
        DiInterface $interface,
        DiParent $parent,
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
