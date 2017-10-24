<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\MaintenanceMode;

class MaintenanceModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MaintenanceMode
     */
    protected $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface  | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $flagDir;

    protected function setup()
    {
        $this->flagDir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->flagDir));

        $this->model = new MaintenanceMode($filesystem);
    }

    public function testIsOnInitial()
    {
        $this->flagDir->expects($this->once())->method('isExist')
            ->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(false));
        $this->assertFalse($this->model->isOn());
    }

    public function testisOnWithoutIP()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, false],
        ];
        $this->flagDir->expects($this->exactly(2))->method('isExist')
            ->will(($this->returnValueMap($mapisExist)));
        $this->assertTrue($this->model->isOn());
    }

    public function testisOnWithIP()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->exactly(2))->method('isExist')
            ->will(($this->returnValueMap($mapisExist)));
        $this->assertTrue($this->model->isOn());
    }

    public function testisOnWithIPNoMaintenance()
    {
        $this->flagDir->expects($this->once())->method('isExist')
            ->with(MaintenanceMode::FLAG_FILENAME)
            ->willReturn(false);
        $this->assertFalse($this->model->isOn());
    }

    public function testMaintenanceModeOn()
    {
        $this->flagDir->expects($this->at(0))->method('isExist')->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(false));
        $this->flagDir->expects($this->at(1))->method('touch')->will($this->returnValue(true));
        $this->flagDir->expects($this->at(2))->method('isExist')->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(true));
        $this->flagDir->expects($this->at(3))->method('isExist')->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue(false));

        $this->assertFalse($this->model->isOn());
        $this->assertTrue($this->model->set(true));
        $this->assertTrue($this->model->isOn());
    }

    public function testMaintenanceModeOff()
    {
        $this->flagDir->expects($this->at(0))->method('isExist')->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(true));
        $this->flagDir->expects($this->at(1))->method('delete')->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(false));
        $this->flagDir->expects($this->at(2))->method('isExist')->with(MaintenanceMode::FLAG_FILENAME)
            ->will($this->returnValue(false));

        $this->assertFalse($this->model->set(false));
        $this->assertFalse($this->model->isOn());
    }

    public function testSetAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->any())->method('isExist')->will($this->returnValueMap($mapisExist));
        $this->flagDir->expects($this->any())->method('writeFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue(true));

        $this->flagDir->expects($this->any())->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue(''));

        $this->model->setAddresses('');
        $this->assertEquals([''], $this->model->getAddressInfo());
    }

    public function testSetSingleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->any())->method('isExist')->will($this->returnValueMap($mapisExist));
        $this->flagDir->expects($this->any())->method('delete')->will($this->returnValueMap($mapisExist));

        $this->flagDir->expects($this->any())->method('writeFile')
            ->will($this->returnValue(10));

        $this->flagDir->expects($this->any())->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue('address1'));

        $this->model->setAddresses('address1');
        $this->assertEquals(['address1'], $this->model->getAddressInfo());
    }

    public function testOnSetMultipleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, true],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->any())->method('isExist')->will($this->returnValueMap($mapisExist));
        $this->flagDir->expects($this->any())->method('delete')->will($this->returnValueMap($mapisExist));

        $this->flagDir->expects($this->any())->method('writeFile')
            ->will($this->returnValue(10));

        $this->flagDir->expects($this->any())->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue('address1,1.2.3.4,192.168.0.0/16,2620:0:2d0:200::7/32,1620:0:2d0:200::7'));

        $expectedArray = ['address1', '1.2.3.4', '192.168.0.0/16', '2620:0:2d0:200::7/32', '1620:0:2d0:200::7'];
        $this->model->setAddresses('address1,1.2.3.4,192.168.0.0/16,2620:0:2d0:200::7/32,1620:0:2d0:200::7');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('1.2.3.4')); // exact match
        $this->assertFalse($this->model->isOn('192.168.22.1')); // range match
        $this->assertTrue($this->model->isOn('192.22.1.1')); // range mismatch
        $this->assertTrue($this->model->isOn('address1')); // not an IP address
        $this->assertTrue($this->model->isOn('172.16.0.4')); // complete mismatch
        $this->assertFalse($this->model->isOn('1620:0:2d0:200::7')); // ipv6 match
        $this->assertFalse($this->model->isOn('1620:0:2d0:200:0:0:0:7')); // ipv6 expanded match
        $this->assertFalse($this->model->isOn('2620::ff43:0:ff')); // ipv6 range match
        $this->assertTrue($this->model->isOn('2720::ff43:0:ff')); // ipv6 range mismatch
    }

    public function testOffSetMultipleAddresses()
    {
        $mapisExist = [
            [MaintenanceMode::FLAG_FILENAME, false],
            [MaintenanceMode::IP_FILENAME, true],
        ];
        $this->flagDir->expects($this->any())->method('isExist')->will($this->returnValueMap($mapisExist));
        $this->flagDir->expects($this->any())->method('delete')->will($this->returnValueMap($mapisExist));

        $this->flagDir->expects($this->any())->method('readFile')
            ->with(MaintenanceMode::IP_FILENAME)
            ->will($this->returnValue('address1,1.2.3.4,192.168.0.0/16,2620:0:2d0:200::7/32,1620:0:2d0:200::7'));

        $expectedArray = ['address1', '1.2.3.4', '192.168.0.0/16', '2620:0:2d0:200::7/32', '1620:0:2d0:200::7'];
        $this->model->setAddresses('address1,1.2.3.4,192.168.0.0/16,2620:0:2d0:200::7/32,1620:0:2d0:200::7');
        $this->assertEquals($expectedArray, $this->model->getAddressInfo());
        $this->assertFalse($this->model->isOn('1.2.3.4')); // exact match
        $this->assertFalse($this->model->isOn('192.168.22.1')); // range match
        $this->assertFalse($this->model->isOn('192.22.1.1')); // range mismatch
        $this->assertFalse($this->model->isOn('address1')); // not an IP address
        $this->assertFalse($this->model->isOn('172.16.0.4')); // complete mismatch
        $this->assertFalse($this->model->isOn('1620:0:2d0:200::7')); // ipv6 match
        $this->assertFalse($this->model->isOn('1620:0:2d0:200:0:0:0:7')); // ipv6 expanded match
        $this->assertFalse($this->model->isOn('2620::ff43:0:ff')); // ipv6 range match
        $this->assertFalse($this->model->isOn('2720::ff43:0:ff')); // ipv6 range mismatch
    }
}
