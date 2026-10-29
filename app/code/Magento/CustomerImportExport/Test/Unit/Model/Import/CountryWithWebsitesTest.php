<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import;

use PHPUnit\Framework\TestCase;
use Magento\CustomerImportExport\Model\Import\CountryWithWebsites;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Config\Share as CustomerShareConfig;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use PHPUnit\Framework\MockObject\MockObject;

class CountryWithWebsitesTest extends TestCase
{
    /**
     * @var CountryCollectionFactory|MockObject
     */
    private $countriesFactoryMock;

    /**
     * @var AllowedCountries|MockObject
     */
    private $allowedCountriesReaderMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CustomerShareConfig|MockObject
     */
    private $shareConfigMock;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsites;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->countriesFactoryMock = $this->createMock(CountryCollectionFactory::class);
        $this->allowedCountriesReaderMock = $this->createMock(AllowedCountries::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->shareConfigMock = $this->createMock(CustomerShareConfig::class);

        $this->countryWithWebsites = new CountryWithWebsites(
            $this->countriesFactoryMock,
            $this->allowedCountriesReaderMock,
            $this->storeManagerMock,
            $this->shareConfigMock
        );
    }

    /**
     * Tests method returns allowed countries for specified website
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetCountiesPerWebsite()
    {
        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->method('getId')->willReturn(1);

        $this->storeManagerMock->method('getWebsites')->willReturn([$websiteMock]);
        $this->allowedCountriesReaderMock->method('getAllowedCountries')
            ->with(ScopeInterface::SCOPE_WEBSITE, 1)
            ->willReturn(['US', 'CA']);

        $countryCollectionMock = $this->createMock(CountryCollection::class);
        $countryCollectionMock->method('addFieldToFilter')
            ->with('country_id', ['in' => [['US', 'CA']]])
            ->willReturnSelf();
        $countryCollectionMock->method('toOptionArray')
            ->willReturn([
                ['value' => 'US', 'label' => 'United States'],
                ['value' => 'CA', 'label' => 'Canada']
            ]);

        $this->countriesFactoryMock->method('create')->willReturn($countryCollectionMock);

        $expectedResult = [
            ['value' => 'US', 'label' => 'United States', 'website_ids' => [1]],
            ['value' => 'CA', 'label' => 'Canada', 'website_ids' => [1]]
        ];

        $this->assertEquals($expectedResult, $this->countryWithWebsites->getCountiesPerWebsite());
    }
}
