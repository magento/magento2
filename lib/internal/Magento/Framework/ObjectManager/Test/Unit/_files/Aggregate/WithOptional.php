<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Aggregate;

class WithOptional
{
    public $parent;

    public $child;

    public function __construct(\Magento\Test\Di\DiParent $parent = null, \Magento\Test\Di\Child $child = null)
    {
        $this->parent = $parent;
        $this->child = $child;
    }
}
