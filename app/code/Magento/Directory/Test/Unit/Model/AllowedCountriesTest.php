<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Customer\Model\Config\Share;
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
     * @var Share|MockObject
     */
    private $shareConfigMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->shareConfigMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * Test for getAllowedCountries with global scope
     */
    public function testGetAllowedCountriesWithGlobalScope()
    {
        $expectedFilter = 1;
        $expectedScope = ScopeInterface::SCOPE_WEBSITES;

        $this->shareConfigMock->expects($this->once())
            ->method('isGlobalScope')
            ->willReturn(true);
        if ($this->shareConfigMock->isGlobalScope()) {

            $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
            $websiteMock->expects($this->once())
                ->method('getId')
                ->willReturn($expectedFilter);

            $this->scopeConfigMock->expects($this->once())
                ->method('getValue')
                ->with(AllowedCountries::ALLOWED_COUNTRIES_PATH, 'website', $websiteMock->getId())
                ->willReturn('AM');

            //$scopeCode should have single valued array only eg:[1]
            $this->assertEquals(
                ['AM' => 'AM'],
                $this->allowedCountriesReader->getAllowedCountries($expectedScope, [$expectedFilter])
            );
        }
    }
}
