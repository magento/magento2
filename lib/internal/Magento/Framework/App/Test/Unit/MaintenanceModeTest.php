<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Event\Manager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MaintenanceModeTest extends TestCase
{
    /**
     * @var MaintenanceMode
     */
    protected $model;

    /**
     * @var WriteInterface|MockObject
     */
    protected $flagDir;

    /**
     * @var Manager|MockObject
     */
    private $eventManager;

    /**
     * @inheritdoc
     */
    protected function setup(): void
    {
        $this->flagDir = $this->getMockForAbstractClass(WriteInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')
            ->willReturn($this->flagDir);
        $this->eventManager = $this->createMock(Manager::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(MaintenanceMode::class, [
            'filesystem' => $filesystem,
            'eventManager' => $this->eventManager,
        ]);
    }

    /**
     * Is On initial test
     *
     * @return void
     */
    public function testIsOnInitial()
    {
        $this->flagDir->expects($this->once())
            ->method('isExist')
            ->with(MaintenanceMode::FLAG_FILENAME)
            ->willReturn(false);
        $this->assertFalse($this->model->isOn());
    }

    /**
     * Is On without ip test
     *
     * @return void
     */
    public function testisOnWithoutIP()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, false],
        ];
        $this->flagDir->expects($this->exactly(2))
            ->method('isExist')
            ->willReturnMap($mapisExist);
        $this->assertTrue($this->model->isOn());
    }

    /**
     * Is On with IP test
     *
     * @return void
     */
    public function testisOnWithIP()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->exactly(2))
            ->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->expects($this->once())
            ->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('');
        $this->assertFalse($this->model->isOn());
    }

    /**
     * Is On with IP but no Maintenance files test
     *
     * @return void
     */
    public function testisOnWithIPNoMaintenance()
    {
        $this->flagDir->expects($this->once())
            ->method('isExist')
            ->with(MaintenanceMode::FLAG_FILENAME)
            ->willReturn(false);
        $this->assertFalse($this->model->isOn());
    }

    /**
     * Maintenance Mode On test
     *
     * Tests common scenario with Full Page Cache is set to On
     *
     * @return void
     */
    public function testMaintenanceModeOn()
    {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('maintenance_mode_changed', ['isOn' => true]);

        $this->flagDir->expects($this->once())
            ->method('touch')
            ->with(MaintenanceMode::FLAG_FILENAME);

        $this->model->set(true);
    }

    /**
     * Maintenance Mode Off test
     *
     * Tests common scenario when before Maintenance Mode Full Page Cache was setted to on
     *
     * @return void
     */
    public function testMaintenanceModeOff()
    {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('maintenance_mode_changed', ['isOn' => false]);

        $this->flagDir->method('isExist')
            ->with(MaintenanceMode::FLAG_FILENAME)
            ->willReturn(true);

        $this->flagDir->expects($this->once())
            ->method('delete')
            ->with(MaintenanceMode::FLAG_FILENAME);

        $this->model->set(false);
    }

    /**
     * Set empty addresses test
     *
     * @return void
     */
    public function testSetAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->method('writeFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn(true);

        $this->flagDir->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('');

        $this->model->setAddresses('');
        $this->assertEquals([''], $this->model->getAddressInfo());
    }

    /**
     * Set single address test
     *
     * @return void
     */
    public function testSetSingleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->method('delete')
            ->willReturnMap($mapisExist);

        $this->flagDir->method('writeFile')
            ->willReturn(10);

        $this->flagDir->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('address1');

        $this->model->setAddresses('address1');
        $this->assertEquals(['address1'], $this->model->getAddressInfo());
    }

    /**
     * Is On when multiple addresses test was setted
     *
     * @return void
     */
    public function testOnSetMultipleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->method('delete')
            ->willReturnMap($mapisExist);

        $this->flagDir->method('writeFile')
            ->willReturn(10);

        $this->flagDir->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('address1,10.50.60.123');

        $expectedArray = ['address1', '10.50.60.123'];
        $this->model->setAddresses('address1,10.50.60.123');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('address1'));
        $this->assertTrue($this->model->isOn('address3'));
    }

    /**
     * Is Off when multiple addresses test was setted
     *
     * @return void
     */
    public function testOffSetMultipleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, false],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->method('delete')
            ->willReturnMap($mapisExist);

        $this->flagDir->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('address1,10.50.60.123');

        $expectedArray = ['address1', '10.50.60.123'];
        $this->model->setAddresses('address1,10.50.60.123');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('address1'));
        $this->assertFalse($this->model->isOn('address3'));
    }
}
