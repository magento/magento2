<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Circular\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param array $chains
     * @return \Magento\Setup\Module\Dependency\Report\Circular\Data\Module
     */
    protected function createModule($name, $chains = [])
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Circular\Data\Module::class,
            ['name' => $name, 'chains' => $chains]
        );
    }

    public function testGetName()
    {
        $name = 'name';
        $module = $this->createModule($name, []);

        $this->assertEquals($name, $module->getName());
    }

    public function testGetChains()
    {
        $chains = ['foo', 'baz', 'bar'];
        $module = $this->createModule('name', $chains);

        $this->assertEquals($chains, $module->getChains());
    }

    public function testGetChainsCount()
    {
        $module = $this->createModule('name', ['foo', 'baz', 'bar']);

        $this->assertEquals(3, $module->getChainsCount());
    }
}
