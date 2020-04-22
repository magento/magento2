<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /**
     * @var \Magento\Store\Model\WebSite|MockObject
     */
    protected $webSiteModel;

    /**
     * @var StoreResolver|MockObject
     */
    protected $storeResolver;

    /**
     * @var  AdvancedPricing\Validator\Website|MockObject
     */
    protected $website;

    protected function setUp(): void
    {
        $this->webSiteModel = $this->getMockBuilder(Website::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeResolver = $this->createPartialMock(
            StoreResolver::class,
            ['getWebsiteCodeToId']
        );

        $this->website = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website::class
        )
            ->setMethods(['getAllWebsitesValue', '_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->storeResolver, $this->webSiteModel])
            ->getMock();
    }

    public function testInit()
    {
        $result = $this->website->init(null);

        $this->assertEquals($this->website, $result);
    }

    /**
     * @dataProvider isValidReturnDataProvider
     *
     * @param array  $value
     * @param string $allWebsites
     * @param string $colTierPriceWebsite
     * @param bool   $expectedResult
     */
    public function testIsValidReturn(
        $value,
        $allWebsites,
        $colTierPriceWebsite,
        $expectedResult
    ) {
        $this->website->expects($this->once())->method('_clearMessages');
        $this->website->method('getAllWebsitesValue')->willReturn($allWebsites);
        $this->storeResolver->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
        ]);

        $result = $this->website->isValid($value);
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

        $this->website->expects($this->once())->method('_clearMessages');
        $this->website->method('getAllWebsitesValue')->willReturn($allWebsitesValue);
        $this->storeResolver->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
        ]);

        $this->website->method('_addMessages')->with($expectedMessages);
        $this->website->isValid($value);
    }

    public function testGetAllWebsitesValue()
    {
        $currencyCode = 'currencyCodeValue';
        $currency = $this->createPartialMock(Currency::class, ['getCurrencyCode']);
        $currency->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);

        $this->webSiteModel->expects($this->once())->method('getBaseCurrency')->willReturn($currency);

        $expectedResult = AdvancedPricing::VALUE_ALL_WEBSITES . ' [' . $currencyCode . ']';
        $websiteString = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website::class
        )
            ->setMethods(['_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->storeResolver, $this->webSiteModel])
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
