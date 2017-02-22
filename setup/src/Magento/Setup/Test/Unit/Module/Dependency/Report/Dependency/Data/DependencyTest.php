<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Dependency\Data;

use \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $module
     * @param string|null $type One of \Magento\Setup\Module\Dependency\Dependency::TYPE_ const
     * @return \Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency
     */
    protected function createDependency($module, $type = null)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Setup\Module\Dependency\Report\Dependency\Data\Dependency',
            ['module' => $module, 'type' => $type]
        );
    }

    public function testGetModule()
    {
        $module = 'module';

        $dependency = $this->createDependency($module);

        $this->assertEquals($module, $dependency->getModule());
    }

    public function testGetType()
    {
        $type = Dependency::TYPE_SOFT;

        $dependency = $this->createDependency('module', $type);

        $this->assertEquals($type, $dependency->getType());
    }

    public function testThatHardTypeIsDefault()
    {
        $dependency = $this->createDependency('module');

        $this->assertEquals(Dependency::TYPE_HARD, $dependency->getType());
    }

    public function testThatHardTypeIsDefaultIfPassedWrongType()
    {
        $dependency = $this->createDependency('module', 'wrong_type');

        $this->assertEquals(Dependency::TYPE_HARD, $dependency->getType());
    }
}
