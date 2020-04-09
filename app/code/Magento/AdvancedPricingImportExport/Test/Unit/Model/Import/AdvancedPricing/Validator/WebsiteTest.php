<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\WebSite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /**
     * @var WebSite|MockObject
     */
    private $webSiteModelMock;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolverMock;

    /**
     * @var AdvancedPricing\Validator\Website|MockObject
     */
    private $websiteMock;

    protected function setUp(): void
    {
        $this->webSiteModelMock = $this->getMockBuilder(Website::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeResolverMock = $this->createPartialMock(
            StoreResolver::class,
            ['getWebsiteCodeToId']
        );

        $this->websiteMock = $this->getMockBuilder(AdvancedPricing\Validator\Website::class)
            ->setMethods(['getAllWebsitesValue', '_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->storeResolverMock, $this->webSiteModelMock])
            ->getMock();
    }

    public function testInit()
    {
        $result = $this->websiteMock->init(null);

        $this->assertEquals($this->websiteMock, $result);
    }

    /**
     * @dataProvider isValidReturnDataProvider
     *
     * @param array $value
     * @param string $allWebsites
     * @param string $colTierPriceWebsite
     * @param bool $expectedResult
     */
    public function testIsValidReturn(
        $value,
        $allWebsites,
        $colTierPriceWebsite,
        $expectedResult
    ) {
        $this->websiteMock->expects($this->once())->method('_clearMessages');
        $this->websiteMock->expects($this->any())->method('getAllWebsitesValue')->willReturn($allWebsites);
        $this->storeResolverMock->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
        ]);

        $result = $this->websiteMock->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsValidReturnAddMessagesCall()
    {
        $value = [
            AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
        ];
        $allWebsitesValue = 'not tier|group price website value';
        $colTierPriceWebsite = false;
        $expectedMessages = [AdvancedPricing\Validator\Website::ERROR_INVALID_WEBSITE];

        $this->websiteMock->expects($this->once())->method('_clearMessages');
        $this->websiteMock->expects($this->any())->method('getAllWebsitesValue')->willReturn($allWebsitesValue);
        $this->storeResolverMock->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
        ]);

        $this->websiteMock->expects($this->any())->method('_addMessages')->with($expectedMessages);
        $this->websiteMock->isValid($value);
    }

    public function testGetAllWebsitesValue()
    {
        $currencyCode = 'currencyCodeValue';
        $currency = $this->createPartialMock(Currency::class, ['getCurrencyCode']);
        $currency->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);

        $this->webSiteModelMock->expects($this->once())->method('getBaseCurrency')->willReturn($currency);

        $expectedResult = AdvancedPricing::VALUE_ALL_WEBSITES . ' [' . $currencyCode . ']';
        $websiteString = $this->getMockBuilder(AdvancedPricing\Validator\Website::class)
            ->setMethods(['_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->storeResolverMock, $this->webSiteModelMock])
            ->getMock();
        $result = $websiteString->getAllWebsitesValue();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isValidReturnDataProvider()
    {
        return [
            // False cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_TIER_PRICE => 'value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => false,
                '$expectedResult' => false,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_TIER_PRICE => 'tier value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => false,
                '$expectedResult' => false,
            ],
            // True cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                ],
                '$allWebsites' => 'tier value',
                '$colTierPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                ],
                '$allWebsites' => 'group value',
                '$colTierPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => false,
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
        ];
    }
}
