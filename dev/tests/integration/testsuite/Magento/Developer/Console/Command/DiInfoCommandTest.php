<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Magento\Framework\App\AreaList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\ObjectManagerInterface;

class DiInfoCommandTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var Information|MockObject
     */
    private Information|MockObject $informationMock;

    /**
     * @var AreaList|MockObject
     */
    private AreaList|MockObject $areaListMock;

    /**
     * @var DiInfoCommand
     */
    private DiInfoCommand $command;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->informationMock = $this->createMock(Information::class);
        $this->areaListMock = $this->createMock(AreaList::class);
        $this->command = new DiInfoCommand($this->informationMock, $this->objectManager, $this->areaListMock);
    }

    /**
     * @return void
     */
    public function testExecuteWithGlobalArea(): void
    {
        $this->informationMock->expects($this->any())
            ->method('getPreference')
            ->with('Magento\Framework\App\RouterList')
            ->willReturn('Magento\Framework\App\RouterList');

        $this->informationMock->expects($this->any())
            ->method('getParameters')
            ->with('Magento\Framework\App\RouterList')
            ->willReturn([
                ['objectManager', 'Magento\Framework\ObjectManagerInterface', null],
                ['routerList', null, null]
            ]);

        $this->informationMock->expects($this->once())
            ->method('getVirtualTypes')
            ->with('Magento\Framework\App\RouterList')
            ->willReturn([]);

        $this->informationMock->expects($this->any())
            ->method('getPlugins')
            ->with('Magento\Framework\App\RouterList')
            ->willReturn([
                'before' => [],
                'around' => [],
                'after' => []
            ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                DiInfoCommand::CLASS_NAME => "Magento\Framework\App\RouterList",
                DiInfoCommand::AREA_CODE => null
            ],
        );
        $this->assertStringContainsString(
            'DI configuration for the class Magento\Framework\App\RouterList in the GLOBAL area',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithAreaCode(): void
    {
        $className = "Magento\Framework\App\RouterList";
        $this->informationMock->expects($this->any())
            ->method('getPreference')
            ->with($className)
            ->willReturn($className);

        $this->informationMock->expects($this->any())
            ->method('getParameters')
            ->with($className)
            ->willReturn([
                ['objectManager', 'Magento\Framework\ObjectManagerInterface', null],
                ['routerList', null, null]
            ]);

        $this->informationMock->expects($this->once())
            ->method('getVirtualTypes')
            ->with($className)
            ->willReturn([]);

        $this->informationMock->expects($this->any())
            ->method('getPlugins')
            ->with($className)
            ->willReturn([
                'before' => [],
                'around' => [],
                'after' => []
            ]);

        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn(['frontend', 'adminhtml']);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                DiInfoCommand::CLASS_NAME => "$className",
                DiInfoCommand::AREA_CODE => "adminhtml"
            ],
        );

        $this->assertStringContainsString(
            "DI configuration for the class $className in the ADMINHTML area",
            $commandTester->getDisplay()
        );
    }
}
