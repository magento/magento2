<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Framework\Data\Dependency;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    /**
     * @param string $lib
     * @param int $count
     * @return Dependency
     */
    protected function createDependency($lib, $count)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            Dependency::class,
            ['lib' => $lib, 'count' => $count]
        );
    }

    public function testGetLib()
    {
        $lib = 'lib';

        $dependency = $this->createDependency($lib, 0);

        $this->assertEquals($lib, $dependency->getLib());
    }

    public function testGetCount()
    {
        $count = 3;

        $dependency = $this->createDependency('lib', $count);

        $this->assertEquals($count, $dependency->getCount());
    }
}
