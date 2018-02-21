<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mview\Test\Unit\Config\Data;

use \Magento\Framework\Mview\Config\Data\Proxy;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config\Data\Proxy
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Mview\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->dataMock = $this->getMock(
            'Magento\Framework\Mview\Config\Data', [], [], '', false
        );
    }

    public function testMergeShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            true
        );

        $this->model->merge(['some_config']);
    }

    public function testMergeNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('merge')
            ->with(['some_config']);

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            false
        );

        $this->model->merge(['some_config']);
    }

    public function testGetShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->will($this->returnValue('some_value'));

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            true
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }

    public function testGetNonShared()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Mview\Config\Data')
            ->will($this->returnValue($this->dataMock));
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_path', 'default')
            ->will($this->returnValue('some_value'));

        $this->model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Mview\Config\Data',
            false
        );

        $this->assertEquals('some_value', $this->model->get('some_path', 'default'));
    }
}
