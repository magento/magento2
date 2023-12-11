<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model;

use Magento\AdvancedPricingImportExport\Model\CurrencyResolver;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

/**
 * Test currency resolver for tier price scope
 */
class CurrencyResolverTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * @var CurrencyResolver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->directoryData = $this->createMock(DirectoryData::class);
        $this->model = new CurrencyResolver(
            $this->storeManager,
            $this->directoryData,
        );
    }

    /**
     * Test that the result of getWebsitesBaseCurrency() should be cached
     */
    public function testShouldCacheResultOfGetWebsitesBaseCurrency(): void
    {
        $expected = [
            'base' => 'EUR',
            'england' => 'GBP',
        ];
        $websites = [];
        foreach ($expected as $websiteCode => $currencyCode) {
            $websites[] = $this->createConfiguredMock(
                Website::class,
                [
                    'getCode' => $websiteCode,
                    'getBaseCurrencyCode' => $currencyCode
                ]
            );
        }
        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);
        $this->assertEquals($expected, $this->model->getWebsitesBaseCurrency());
        $this->assertEquals($expected, $this->model->getWebsitesBaseCurrency());
    }

    /**
     * Test that the result of getDefaultBaseCurrency() should be cached
     */
    public function testShouldCacheResultOfGetDefaultBaseCurrency(): void
    {
        $expected = 'USD';
        $this->directoryData->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->model->getDefaultBaseCurrency());
        $this->assertEquals($expected, $this->model->getDefaultBaseCurrency());
    }
}
