<?php declare(strict_types=1);
/**
 * Test class for \Magento\Store\Model\Store\StoresConfig
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoresConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoresConfigTest extends TestCase
{
    /**
     * @var StoresConfig
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_storeManager;

    /**
     * @var MockObject
     */
    protected $_storeOne;

    /**
     * @var MockObject
     */
    protected $_storeTwo;

    /**
     * @var MockObject
     */
    protected $_config;

    protected function setUp(): void
    {
        $this->_storeOne = $this->createMock(Store::class);
        $this->_storeTwo = $this->createMock(Store::class);
        $this->_storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->_config = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->_model = new StoresConfig(
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
