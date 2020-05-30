<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\StoreConfigurationProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreConfigurationProviderTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var WebsiteInterface|MockObject
     */
    private $websiteMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var StoreConfigurationProvider|MockObject
     */
    private $storeConfigurationProvider;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->storeManagerMock =  $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->websiteMock =  $this->getMockForAbstractClass(WebsiteInterface::class);

        $this->storeMock =  $this->getMockForAbstractClass(StoreInterface::class);

        $this->configPaths = [
            'web/unsecure/base_url',
            'currency/options/base',
            'general/locale/timezone'
        ];

        $this->storeConfigurationProvider = new StoreConfigurationProvider(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->configPaths
        );
    }

    public function testGetReport()
    {
        $map = [
            ['web/unsecure/base_url', 'default', 0, '127.0.0.1'],
            ['currency/options/base', 'default', 0, 'USD'],
            ['general/locale/timezone', 'default', 0, 'America/Dawson'],
            ['web/unsecure/base_url', 'websites', 1, '127.0.0.2'],
            ['currency/options/base', 'websites', 1, 'USD'],
            ['general/locale/timezone', 'websites', 1, 'America/Belem'],
            ['web/unsecure/base_url', 'stores', 2, '127.0.0.3'],
            ['currency/options/base', 'stores', 2, 'USD'],
            ['general/locale/timezone', 'stores', 2, 'America/Phoenix'],
        ];

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap($map);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->websiteMock]);

        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->storeMock]);

        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $result = iterator_to_array($this->storeConfigurationProvider->getReport());
        $resultValues = [];
        foreach ($result as $item) {
            $resultValues[] = array_values($item);
        }
        array_multisort($resultValues);
        array_multisort($map);
        $this->assertEquals($resultValues, $map);
    }
}
