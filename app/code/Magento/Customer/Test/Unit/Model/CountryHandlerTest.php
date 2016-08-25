<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\CountryHandler;
use Magento\Directory\Model\CountryHandlerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryHandlerInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Share
     */
    private $configShareMock;

    /**
     * @var CountryHandlerInterface
     */
    private $countryHandler;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);
        $this->configShareMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryHandler = new CountryHandler(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->configShareMock
        );
    }

    public function testGetAllowedCountriesInGlobalScope()
    {
        $filter = "bugaga";
        $scope = "default";
        $website1 = $this->getMock(WebsiteInterface::class);
        $website2 = $this->getMock(WebsiteInterface::class);

        $website1->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $website2->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website1, $website2]);

        $this->configShareMock->expects($this->atLeastOnce())
            ->method('isGlobalScope')
            ->willReturn(true);

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 1],
                [CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 2]
            )
            ->willReturnMap([
                [CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 1, 'AZ,AM'],
                [CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 2, 'AF,AM']
            ]);

        $expected = [
            'AZ' => 'AZ',
            'AM' => 'AM',
            'AF' => 'AF'
        ];

        $this->assertEquals($expected, $this->countryHandler->getAllowedCountries($filter, $scope));
    }

    public function testGetAllowedCountriesWithEmptyFilter()
    {
        $website1 = $this->getMock(WebsiteInterface::class);
        $website1->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($website1);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $this->assertEquals(['AM' => 'AM'], $this->countryHandler->getAllowedCountries());
    }

    public function testGetAllowedCountriesWithoutGlobalScope()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $this->configShareMock->expects($this->atLeastOnce())
            ->method('isGlobalScope')
            ->willReturn(true);

        $this->assertEquals(
            ['AM' => 'AM'],
            $this->countryHandler->getAllowedCountries(1, ScopeInterface::SCOPE_WEBSITE, true)
        );
    }

    public function testLoadByScope()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(CountryHandlerInterface::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('country_id', ['in' => ['AM' => 'AM']]);

        $this->assertEquals($collectionMock,
            $this->countryHandler->loadByScope($collectionMock, 1, ScopeInterface::SCOPE_WEBSITE));
    }
}
