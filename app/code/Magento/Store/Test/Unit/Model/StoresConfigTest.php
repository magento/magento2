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

    /**
     * @inheritdoc
     */
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

    /**
     * @return void
     */
    public function testGetStoresConfigByPath(): void
    {
        $path = 'config/path';

        $this->_storeOne
            ->method('getCode')
            ->willReturn('code_0');
        $this->_storeOne
            ->method('getId')
            ->willReturn(0);

        $this->_storeTwo
            ->method('getCode')
            ->willReturn('code_1');
        $this->_storeTwo
            ->method('getId')
            ->willReturn(1);

        $this->_storeManager
            ->expects($this->once())
            ->method('getStores')
            ->with(true)
            ->willReturn([0 => $this->_storeOne, 1 => $this->_storeTwo]);

        $this->_config
            ->method('getValue')
            ->withConsecutive([$path, 'store', 'code_0'], [$path, 'store', 'code_1'])
            ->willReturnOnConsecutiveCalls(0, 1);

        $this->assertEquals([0 => 0, 1 => 1], $this->_model->getStoresConfigByPath($path));
    }
}
