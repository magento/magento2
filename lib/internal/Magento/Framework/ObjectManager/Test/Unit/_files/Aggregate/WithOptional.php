<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Di\Aggregate;

use Magento\Test\Di\Child;
use Magento\Test\Di\DiParent;

class WithOptional
{
    public $parent;

    public $child;

    /**
     * WithOptional constructor.
     * @param DiParent|null $parent
     * @param Child|null $child
     */
    public function __construct(?DiParent $parent = null, ?Child $child = null)
    {
        $this->parent = $parent;
        $this->child = $child;
    }
}
