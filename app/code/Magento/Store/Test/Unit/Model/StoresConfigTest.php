<?php
/**
 * Test class for \Magento\Store\Model\Store\StoresConfig
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

class StoresConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoresConfig
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeOne;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeTwo;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_config;

    protected function setUp(): void
    {
        $this->_storeOne = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_storeTwo = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_config = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_model = new \Magento\Store\Model\StoresConfig(
            $this->_storeManager,
            $this->_config
        );
    }

    public function testGetStoresConfigByPath()
    {
        $path = 'config/path';

        $this->_storeOne
            ->expects($this->at(0))
            ->method('getCode')
            ->willReturn('code_0');

        $this->_storeOne
            ->expects($this->at(1))
            ->method('getId')
            ->willReturn(0);

        $this->_storeTwo
            ->expects($this->at(0))
            ->method('getCode')
            ->willReturn('code_1');

        $this->_storeTwo
            ->expects($this->at(1))
            ->method('getId')
            ->willReturn(1);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStores')
            ->with(true)
            ->willReturn([0 => $this->_storeOne, 1 => $this->_storeTwo]);

        $this->_config
            ->expects($this->at(0))
            ->method('getValue')
            ->with($path, 'store', 'code_0')
            ->willReturn(0);

        $this->_config
            ->expects($this->at(1))
            ->method('getValue')
            ->with($path, 'store', 'code_1')
            ->willReturn(1);

        $this->assertEquals([0 => 0, 1 => 1], $this->_model->getStoresConfigByPath($path));
    }
}
