<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\Utility\IPAddress;
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
            'ipAddress' => $objectManager->getObject(IPAddress::class),
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
        $this->flagDir->expects($this->once())
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
        $this->flagDir->expects($this->exactly(3))
            ->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->expects($this->once())
            ->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn('127.0.0.1');

        $this->assertFalse($this->model->isOn('127.0.0.1'));
        $this->assertTrue($this->model->isOn());
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
     * Set single IPv4 address test
     *
     * @return void
     */
    public function testSetSingleAddressV4Legacy(): void
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
            ->willReturn('198.51.100.3');

        $this->model->setAddresses('198.51.100.3');
        $this->assertEquals(['198.51.100.3'], $this->model->getAddressInfo());
    }

    /**
     * Set single IPv4 address test
     *
     * @return void
     */
    public function testSetSingleAddressV4(): void
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
            ->willReturn('198.51.100.1/32');

        $this->model->setAddresses('198.51.100.1');
        $this->assertEquals(['198.51.100.1/32'], $this->model->getAddressInfo());
    }

    /**
     * Set single IPv6 address test
     *
     * @return void
     */
    public function testSetSingleAddressV6Legacy(): void
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
            ->willReturn('2001:db8::6');

        $this->model->setAddresses('2001:db8::6');
        $this->assertEquals(['2001:db8::6'], $this->model->getAddressInfo());
    }

    /**
     * Set single IPv6 address test
     *
     * @return void
     */
    public function testSetSingleAddressV6(): void
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
            ->willReturn('2001:db8::1/128');

        $this->model->setAddresses('2001:db8::1');
        $this->assertEquals(['2001:db8::1/128'], $this->model->getAddressInfo());
    }

    /**
     * Set single IPv4 address range test
     *
     * @return void
     */
    public function testSetSingleAddressRangeV4(): void
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
            ->willReturn('198.51.100.0/24');

        $this->model->setAddresses('198.51.100.0/24');
        $this->assertEquals(['198.51.100.0/24'], $this->model->getAddressInfo());
    }

    /**
     * Set single IPv6 address range test
     *
     * @return void
     */
    public function testSetSingleAddressRangeV6(): void
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
            ->willReturn('2001:db8::/64');

        $this->model->setAddresses('2001:db8::/64');
        $this->assertEquals(['2001:db8::/64'], $this->model->getAddressInfo());
    }

    /**
     * Is On when multiple addresses test was setted
     *
     * @return void
     */
    public function testOnSetMultipleAddressesV4(): void
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
            ->willReturn('203.0.113.71/32,10.50.60.123/32');

        $expectedArray = ['203.0.113.71/32', '10.50.60.123/32'];
        $this->model->setAddresses('203.0.113.71,10.50.60.123');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('203.0.113.71'));
        $this->assertTrue($this->model->isOn('198.51.100.85'));
    }

    /**
     * Is On when multiple IPv6 addresses test was setted
     *
     * @return void
     */
    public function testOnSetMultipleAddressesV6(): void
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
            ->willReturn('2001:db8::1/128,2001:db8::ae/128');

        $expectedArray = ['2001:db8::1/128', '2001:db8::ae/128'];
        $this->model->setAddresses('2001:db8::1,2001:db8::ae');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('2001:db8::1'));
        $this->assertTrue($this->model->isOn('2001:db8::ff'));
    }

    /**
     * Is On when IPv4 & IPv6 addresses are set test
     *
     * @return void
     */
    public function testOnSetMultipleAddressesMixed(): void
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
            ->willReturn('203.0.113.71/32,2001:db8::ae/128');

        $expectedArray = ['203.0.113.71/32', '2001:db8::ae/128'];
        $this->model->setAddresses('203.0.113.71,2001:db8::ae');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('203.0.113.71'));
        $this->assertFalse($this->model->isOn('2001:db8::ae'));
        $this->assertTrue($this->model->isOn('198.51.100.85'));
        $this->assertTrue($this->model->isOn('2001:db8::1'));
    }

    /**
     * Is On when multiple address ranges test was setted
     *
     * @return void
     */
    public function testOnSetMultipleAddressRangesV4(): void
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
            ->willReturn('203.0.113.68/30,10.50.60.64/26');

        $expectedArray = ['203.0.113.68/30', '10.50.60.64/26'];
        $this->model->setAddresses('203.0.113.68/30,10.50.60.64/26');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('203.0.113.71'));
        $this->assertTrue($this->model->isOn('198.51.100.85'));
    }

    /**
     * Is On when multiple IPv6 address ranges test was setted
     *
     * @return void
     */
    public function testOnSetMultipleAddressRangesV6(): void
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
            ->willReturn('2001:db8::/96,2001:db8::ae/116');

        $expectedArray = ['2001:db8::/96', '2001:db8::ae/116'];
        $this->model->setAddresses('2001:db8::/96,2001:db8::ae/116');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('2001:db8::1'));
        $this->assertTrue($this->model->isOn('2001:db8:ff::54b1'));
    }

    /**
     * Is On when IPv4 & IPv6 address ranges are set test
     *
     * @return void
     */
    public function testOnSetMultipleAddressRangesMixed(): void
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
            ->willReturn('203.0.113.64/28,2001:db8:ae::/108');

        $expectedArray = ['203.0.113.64/28', '2001:db8:ae::/108'];
        $this->model->setAddresses('203.0.113.64/28,2001:db8:ae::/108');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('203.0.113.71'));
        $this->assertFalse($this->model->isOn('2001:db8:ae::ce51'));
        $this->assertTrue($this->model->isOn('198.51.100.85'));
        $this->assertTrue($this->model->isOn('2001:db8::1'));
    }

    /**
     * Is Off when multiple addresses test was setted
     *
     * @return void
     */
    public function testOffSetMultipleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->method('isExist')
            ->willReturnMap($mapisExist);
        $this->flagDir->method('delete')
            ->willReturnMap($mapisExist);

        $inputString = '127.0.0.1,203.0.113.71/32,10.50.60.123/32';
        $this->flagDir->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->willReturn($inputString);

        $expectedArray = ['127.0.0.1', '203.0.113.71/32', '10.50.60.123/32'];
        $this->model->setAddresses($inputString);
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('127.0.0.1'));
        $this->assertTrue($this->model->isOn('127.0.0.2'));
        $this->assertFalse($this->model->isOn('203.0.113.71'));
        $this->assertTrue($this->model->isOn('198.51.100.85'));
    }
}
