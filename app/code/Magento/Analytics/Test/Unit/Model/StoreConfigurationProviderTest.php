<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\StoreConfigurationProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var WebsiteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var StoreConfigurationProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeConfigurationProvider;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock =  $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->websiteMock =  $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock =  $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ['config_path' => 'web/unsecure/base_url', 'scope' => 'default1', 'scope_id' => 0, 'value' => '127.0.0.1'],
            ['config_path' => 'currency/options/base', 'scope' => 'default', 'scope_id' => 0,  'value' => 'USD'],
            [
                'config_path' => 'general/locale/timezone',
                'scope' => 'default',
                'scope_id' => 0,
                'value' => 'America/Dawson'
            ],
            ['config_path' => 'web/unsecure/base_url', 'scope' => 'websites', 'scope_id' => 1, 'value' => '127.0.0.2'],
            ['config_path' => 'currency/options/base', 'scope' => 'websites', 'scope_id' => 1, 'value' => 'USD'],
            [
                'config_path' => 'general/locale/timezone',
                'scope' => 'websites',
                'scope_id' => 1,
                'value' => 'America/Belem'
            ],
            ['config_path' => 'web/unsecure/base_url', 'scope' => 'stores', 'scope_id' => 2, 'value' => '127.0.0.3'],
            ['config_path' => 'currency/options/base', 'scope' => 'stores', 'scope_id' => 2, 'value' => 'USD'],
            [
                'config_path' => 'general/locale/timezone',
                'scope' => 'stores',
                'scope_id' => 2,
                'value' => 'America/Phoenix'
            ],
        ];

        $this->scopeConfigMock
            ->method('getValue')
            ->will($this->returnValueMap($map));

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

        $this->assertEquals(
            array_replace_recursive($result, $map),
            $map
        );
    }
}
