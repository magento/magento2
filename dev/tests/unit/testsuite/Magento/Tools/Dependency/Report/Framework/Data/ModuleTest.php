<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Framework\Data;

use Magento\TestFramework\Helper\ObjectManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param array $dependencies
     * @return \Magento\Tools\Dependency\Report\Framework\Data\Module
     */
    protected function createModule($name, $dependencies = [])
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Framework\Data\Module',
            ['name' => $name, 'dependencies' => $dependencies]
        );
    }

    public function testGetName()
    {
        $name = 'name';
        $module = $this->createModule($name, []);

        $this->assertEquals($name, $module->getName());
    }

    public function testGetDependencies()
    {
        $dependencies = ['foo', 'baz', 'bar'];
        $module = $this->createModule('name', $dependencies);

        $this->assertEquals($dependencies, $module->getDependencies());
    }

    public function testGetDependenciesCount()
    {
        $module = $this->createModule('name', ['foo', 'baz', 'bar']);

        $this->assertEquals(3, $module->getDependenciesCount());
    }
}
