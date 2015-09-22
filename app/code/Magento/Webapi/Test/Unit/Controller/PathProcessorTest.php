<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller;

class PathProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface */
    private $storeManagerMock;

    /** @var \Magento\Webapi\Controller\PathProcessor */
    private $model;

    /** @var string */
    private $storeCode = 'myStoreCode';

    public function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->
        expects($this->once())
            ->method('getStores')
            ->willReturn([$this->storeCode => 'store object']);
        $this->model = new \Magento\Webapi\Controller\PathProcessor($this->storeManagerMock);
    }

    public function testAllStoreCode()
    {
        $endpointPath = '/V1/path/of/endpoint';
        $inPath = 'rest/all' . $endpointPath;
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with(\Magento\Store\Model\Store::ADMIN_CODE);
        $result = $this->model->process($inPath);
        $this->assertSame($endpointPath, $result);
    }

    public function testDefaultStoreCode()
    {
        $endpointPath = '/V1/path/of/endpoint';
        $inPath = 'rest' . $endpointPath;
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with(\Magento\Store\Model\Store::DEFAULT_CODE);
        $result = $this->model->process($inPath);
        $this->assertSame($endpointPath, $result);
    }

    public function testArbitraryStoreCode()
    {
        $endpointPath = '/V1/path/of/endpoint';
        $inPath = 'rest/' . $this->storeCode . $endpointPath;
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($this->storeCode);
        $result = $this->model->process($inPath);
        $this->assertSame($endpointPath, $result);
    }
}
