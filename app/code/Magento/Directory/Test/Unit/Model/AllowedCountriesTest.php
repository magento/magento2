<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AllowedCountriesTest extends \PHPUnit_Framework_TestCase
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
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);

        $this->allowedCountriesReader = new AllowedCountries(
            $this->scopeConfigMock,
            $this->storeManagerMock
        );
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
            ->with(AllowedCountries::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $this->assertEquals(['AM' => 'AM'], $this->allowedCountriesReader->getAllowedCountries());
    }

    public function testGetAllowedCountries()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AllowedCountries::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $this->assertEquals(
            ['AM' => 'AM'],
            $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, 1)
        );
    }
}
