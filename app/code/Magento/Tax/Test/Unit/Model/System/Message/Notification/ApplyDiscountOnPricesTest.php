<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\System\Message\Notification;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notification\ApplyDiscountOnPrices as ApplyDiscountOnPricesNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notification\ApplyDiscountOnPrices
 *
 * @SuppressWarnings(PHPMD)
 */
class ApplyDiscountOnPricesTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ApplyDiscountOnPricesNotification
     */
    private $applyDiscountOnPricesNotification;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig|MockObject
     */
    private $taxConfigMock;

    protected function setUp(): void
    {
        parent::setUp();

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getName')->willReturn('testWebsiteName');
        $storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['getWebsite', 'getName']
        );
        $storeMock->expects($this->any())->method('getName')->willReturn('testStoreName');
        $storeMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->applyDiscountOnPricesNotification = (new ObjectManager($this))->getObject(
            ApplyDiscountOnPricesNotification::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    #[DataProvider('dataProviderIsDisplayed')]
    public function testIsDisplayed(
        bool $isWrongApplyDiscountSettingIgnored,
        bool $priceIncludesTax,
        bool $applyTaxAfterDiscount,
        bool $discountTax,
        bool $expectedResult
    ): void {
        $this->taxConfigMock->expects($this->any())->method('isWrongApplyDiscountSettingIgnored')
            ->willReturn($isWrongApplyDiscountSettingIgnored);
        $this->taxConfigMock->expects($this->any())->method('priceIncludesTax')->willReturn($priceIncludesTax);
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')
            ->willReturn($applyTaxAfterDiscount);
        $this->taxConfigMock->expects($this->any())->method('discountTax')->willReturn($discountTax);

        $this->assertEquals($expectedResult, $this->applyDiscountOnPricesNotification->isDisplayed());
    }

    /**
     * @return array
     */
    public static function dataProviderIsDisplayed(): array
    {
        return [
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                false, // $discountTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                false, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongApplyDiscountSettingIgnored,
                true, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ],
            [
                true, // $isWrongApplyDiscountSettingIgnored,
                false, // $priceIncludesTax,
                true, // $applyTaxAfterDiscount,
                true, // $discountTax,
                false // $expectedResult
            ]
        ];
    }

    public function testGetText(): void
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongApplyDiscountSettingIgnored')->willReturn(false);

        $this->taxConfigMock->expects($this->any())->method('priceIncludesTax')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')->willReturn(true);
        $this->taxConfigMock->expects($this->any())->method('discountTax')->willReturn(true);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('tax/tax/ignoreTaxNotification', ['section' => 'apply_discount'])
            ->willReturn('http://example.com');
        $this->applyDiscountOnPricesNotification->isDisplayed();
        $this->assertEquals(
            '<strong>To apply the discount on prices including tax and apply the tax after discount, '
            . 'set Catalog Prices to “Including Tax”. </strong><p>Store(s) affected: testWebsiteName '
            . '(testStoreName)</p><p>Click on the link to '
            . '<a href="http://example.com">ignore this notification</a></p>',
            $this->applyDiscountOnPricesNotification->getText()
        );
    }
}
