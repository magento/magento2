<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\UninstallCollector;

class UninstallCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectUninstall()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $setup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $adapterInterface = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            false
        );
        $select = $this->getMock('Magento\Framework\DB\Select', ['from'], [], '', false);
        $adapterInterface->expects($this->once())->method('select')->willReturn($select);
        $setup->expects($this->exactly(2))->method('getConnection')->willReturn($adapterInterface);
        $result = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->once())->method('from')->willReturn($result);
        $adapterInterface->expects($this->once())
            ->method('fetchAll')
            ->with($result)
            ->willReturn([['module' => 'Magento_A'], ['module' => 'Magento_B'], ['module' => 'Magento_C']]);

        $uninstallA = 'Uninstall Class A';
        $uninstallB = 'Uninstall Class B';
        $objectManager->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap([
                    ['Magento\A\Setup\Uninstall', [], $uninstallA],
                    ['Magento\B\Setup\Uninstall', [], $uninstallB],
                ])
            );
        $objectManager->expects($this->any())->method('get')
            ->with('Magento\Setup\Module\DataSetup')
            ->willReturn($setup);

        $collector = new UninstallCollector($objectManagerProvider);
        $this->assertEquals(['Magento_A' => 'Uninstall Class A'], $collector->collectUninstall());
    }
}

namespace Magento\Setup\Model;

/**
 * This function overrides the native function for the purpose of testing
 *
 * @param string $obj
 * @param string $className
 * @return bool
 */
function is_subclass_of($obj, $className)
{
    if ($obj == 'Uninstall Class A' && $className == 'Magento\Framework\Setup\UninstallInterface') {
        return true;
    }
    return false;
}

/**
 * This function overrides the native function for the purpose of testing
 *
 * @param string $className
 * @return bool
 */
function class_exists($className)
{
    if ($className == 'Magento\A\Setup\Uninstall' || $className == 'Magento\B\Setup\Uninstall') {
        return true;
    }
    return false;
}
