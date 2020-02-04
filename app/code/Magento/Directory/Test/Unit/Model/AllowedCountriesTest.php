<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AllowedCountriesTest extends \PHPUnit\Framework\TestCase
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

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->allowedCountriesReader = new AllowedCountries(
            $this->scopeConfigMock,
            $this->storeManagerMock
        );
    }

    /**
     * Test for getAllowedCountries
     */
    public function testGetAllowedCountriesWithEmptyFilter()
    {
        $website1 = $this->createMock(WebsiteInterface::class);
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

    /**
     * Test for getAllowedCountries
     */
    public function testGetAllowedCountries()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AllowedCountries::ALLOWED_COUNTRIES_PATH, 'website', 1)
            ->willReturn('AM');

        $this->assertEquals(
            ['AM' => 'AM'],
            $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, true)
        );
    }

    /**
     * Test for getAllowedCountries
     */
    public function testGetAllowedCountriesDefaultScope()
    {
        $this->storeManagerMock->expects($this->never())
            ->method('getStore');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(AllowedCountries::ALLOWED_COUNTRIES_PATH, ScopeInterface::SCOPE_STORE, 0)
            ->willReturn('AM');

        $this->assertEquals(
            ['AM' => 'AM'],
            $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, 0)
        );
    }
}
