<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AllowedCountriesTest extends TestCase
{
    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

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
        $website1 = $this->getMockForAbstractClass(WebsiteInterface::class);
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
