<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Framework\Data;

use Magento\TestFramework\Helper\ObjectManager;

class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $lib
     * @param int $count
     * @return \Magento\Tools\Dependency\Report\Framework\Data\Dependency
     */
    protected function createDependency($lib, $count)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Framework\Data\Dependency',
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
