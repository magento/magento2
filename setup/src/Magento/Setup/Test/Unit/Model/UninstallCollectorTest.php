<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\UninstallCollector;

class UninstallCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $adapterInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Select
     */
    private $result;

    protected function setUp(): void
    {
        require_once '_files/app/code/Magento/A/Setup/Uninstall.php';
        require_once '_files/app/code/Magento/B/Setup/Uninstall.php';

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

        $uninstallA = 'Magento\A\Setup\Uninstall';
        $uninstallB = 'Magento\B\Setup\Uninstall';
        $objectManager->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    ['Magento\A\Setup\Uninstall', [], $uninstallA],
                    ['Magento\B\Setup\Uninstall', [], $uninstallB],
                ]
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
            ['Magento_A' => 'Magento\A\Setup\Uninstall', 'Magento_B' => 'Magento\B\Setup\Uninstall'],
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

        $this->assertEquals(
            ['Magento_A' => 'Magento\A\Setup\Uninstall'],
            $this->collector->collectUninstall(['Magento_A'])
        );
    }
}
