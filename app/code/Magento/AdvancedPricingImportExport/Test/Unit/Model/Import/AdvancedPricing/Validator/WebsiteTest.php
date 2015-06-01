<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\WebSite|\PHPUnit_Framework_MockObject_MockObject
     */
     protected $webSiteModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolver;

    /**
     * @var  AdvancedPricing\Validator\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website;

    public function setUp()
    {
        $this->webSiteModel = $this->getMock(
            '\Magento\Store\Model\WebSite',
            ['getBaseCurrency'],
            [],
            '',
            false
        );
        $this->storeResolver = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\StoreResolver',
            ['getWebsiteCodeToId'],
            [],
            '',
            false
        );

        $this->website = $this->getMock(
            '\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website',
            ['getAllWebsitesValue', '_clearMessages', '_addMessages'],
            [
                $this->storeResolver,
                $this->webSiteModel,
            ],
            ''
        );
    }

    public function testInit()
    {
        $result = $this->website->init();

        $this->assertEquals($this->website, $result);
    }

    /**
     * @dataProvider isValidReturnDataProvider
     *
     * @param array  $value
     * @param string $allWebsitesValue
     * @param string $colTierPriceWebsite
     * @param string $colGroupPriceWebsite
     * @param bool   $expectedResult
     */
    public function testIsValidReturn(
        $value,
        $allWebsites,
        $colTierPriceWebsite,
        $colGroupPriceWebsite,
        $expectedResult
    ) {
        $this->website->expects($this->once())->method('_clearMessages');
        $this->website->expects($this->atLeastOnce())->method('getAllWebsitesValue')->willReturn($allWebsites);
        $this->storeResolver->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
            [$value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE], $colGroupPriceWebsite],
        ]);

        $result = $this->website->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsValidReturnAddMessagesCall()
    {
        $value = [
            AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
            AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
        ];
        $allWebsitesValue = 'not tier|group price website value';
        $colTierPriceWebsite = false;
        $colGroupPriceWebsite = 'value';
        $expectedMessages = [AdvancedPricing\Validator\Website::ERROR_INVALID_WEBSITE];

        $this->website->expects($this->once())->method('_clearMessages');
        $this->website->expects($this->atLeastOnce())->method('getAllWebsitesValue')->willReturn($allWebsitesValue);
        $this->storeResolver->method('getWebsiteCodeToId')->willReturnMap([
            [$value[AdvancedPricing::COL_TIER_PRICE_WEBSITE], $colTierPriceWebsite],
            [$value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE], $colGroupPriceWebsite],
        ]);

        $this->website->expects($this->once())->method('_addMessages')->with($expectedMessages);
        $this->website->isValid($value);
    }

    public function testGetAllWebsitesValue()
    {
        $currencyCode = 'currencyCodeValue';
        $currency = $this->getMock('\Magento\Directory\Model\Currency', ['getCurrencyCode'], [], '', false);
        $currency->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);

        $this->webSiteModel->expects($this->once())->method('getBaseCurrency')->willReturn($currency);

        $expectedResult = AdvancedPricing::VALUE_ALL_WEBSITES . ' [' . $currencyCode . ']';

        $website = $this->getMock(
            '\Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website',
            null,
            [
                $this->storeResolver,
                $this->webSiteModel,
            ],
            ''
        );

        $result = $website->getAllWebsitesValue();
        $this->assertEquals($expectedResult, $result);
    }

    public function isValidReturnDataProvider()
    {
        return [
            // False cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => false,
                '$colGroupPriceWebsite' => 'value',
                '$expectedResult' => false,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => false,
                '$expectedResult' => false,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => false,
                '$expectedResult' => false,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => false,
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => false,
                '$expectedResult' => false,
            ],
            // True cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'tier value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'group value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => false,
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'group value',
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'tier value',
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => false,
                ],
                '$allWebsites' => 'not tier|group price website value',
                '$colTierPriceWebsite' => 'value',
                '$colGroupPriceWebsite' => 'value',
                '$expectedResult' => true,
            ],
        ];
    }
}
