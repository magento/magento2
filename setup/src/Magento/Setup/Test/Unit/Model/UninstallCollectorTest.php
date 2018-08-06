<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\UninstallCollector;

class UninstallCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $adapterInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Select
     */
    private $result;

    public function setUp()
    {
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $setup = $this->createMock(\Magento\Setup\Module\DataSetup::class);
        $this->adapterInterface = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );
        $select = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['from']);
        $this->adapterInterface->expects($this->once())->method('select')->willReturn($select);
        $setup->expects($this->exactly(2))->method('getConnection')->willReturn($this->adapterInterface);
        $this->result = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->once())->method('from')->willReturn($this->result);

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
        $setupFactory = $this->createMock(\Magento\Setup\Module\DataSetupFactory::class);
        $setupFactory->expects($this->once())->method('create')->willReturn($setup);

        $this->collector = new UninstallCollector($objectManagerProvider, $setupFactory);
    }

    public function testUninstallCollector()
    {
        $this->result->expects($this->never())->method('where');
        $this->adapterInterface->expects($this->once())
            ->method('fetchAll')
            ->with($this->result)
            ->willReturn([['module' => 'Magento_A'], ['module' => 'Magento_B'], ['module' => 'Magento_C']]);

        $this->assertEquals(
            ['Magento_A' => 'Uninstall Class A', 'Magento_B' => 'Uninstall Class B'],
            $this->collector->collectUninstall()
        );
    }

    public function testUninstallCollectorWithInput()
    {
        $this->result->expects($this->once())->method('where')->willReturn($this->result);
        $this->adapterInterface->expects($this->once())
            ->method('fetchAll')
            ->with($this->result)
            ->willReturn([['module' => 'Magento_A']]);

        $this->assertEquals(['Magento_A' => 'Uninstall Class A'], $this->collector->collectUninstall(['Magento_A']));
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
    if ($obj == 'Uninstall Class A' && $className == \Magento\Framework\Setup\UninstallInterface::class) {
        return true;
    }
    if ($obj == 'Uninstall Class B' && $className == \Magento\Framework\Setup\UninstallInterface::class) {
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
