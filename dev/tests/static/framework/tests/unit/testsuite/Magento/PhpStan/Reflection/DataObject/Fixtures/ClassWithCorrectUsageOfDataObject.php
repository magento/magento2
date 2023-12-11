<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PhpStan\Reflection\DataObject\Fixtures;

use Magento\Framework\DataObject;

class ClassWithCorrectUsageOfDataObject
{
    /**
     * @var DataObject
     */
    private $container;

    /**
     * ClassWithCorrectUsageOfDataObject constructor.
     */
    public function __construct()
    {
        $this->container = new DataObject();
    }

    /**
     * Process with amazing stuff.
     *
     * @return void
     */
    public function doStuff(): void
    {
        $a = $this->container->getSomething('1');

        if ($this->container->hasSomething()) {
            $this->container->setSomething2($a);
        } else {
            $this->container->unsetSomething();
            $this->container->setSometing2($this->container->getStuff());
        }
    }
}
