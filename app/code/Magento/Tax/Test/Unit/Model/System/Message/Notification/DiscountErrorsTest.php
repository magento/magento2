<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\System\Message\Notification;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notification\DiscountErrors as DiscountErrorsNotification;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notification\DiscountErrors
 */
class DiscountErrorsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DiscountErrorsNotification
     */
    private $discountErrorsNotification;

    /**
     * @var StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig | \PHPUnit\Framework\MockObject\MockObject
     */
    private $taxConfigMock;

    protected function setUp(): void
    {
        parent::setUp();

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getName')->willReturn('testWebsiteName');
        $storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getWebsite', 'getName']
        );
        $storeMock->expects($this->any())->method('getName')->willReturn('testStoreName');
        $storeMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->discountErrorsNotification = (new ObjectManager($this))->getObject(
            DiscountErrorsNotification::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    public function testIsDisplayed()
    {
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('isWrongDiscountSettingsIgnored')->willReturn(false);
        $this->assertTrue($this->discountErrorsNotification->isDisplayed());
    }

    public function testIsDisplayedIgnoreWrongConfiguration()
    {
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('isWrongDiscountSettingsIgnored')->willReturn(true);
        $this->assertFalse($this->discountErrorsNotification->isDisplayed());
    }

    public function testGetText()
    {
        $this->taxConfigMock->expects($this->any())->method('applyTaxAfterDiscount')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('isWrongDiscountSettingsIgnored')->willReturn(false);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('tax/tax/ignoreTaxNotification', ['section' => 'discount'])
            ->willReturn('http://example.com');
        $this->discountErrorsNotification->isDisplayed();
        $this->assertEquals(
            '<strong>With customer tax applied “Before Discount”, the final discount calculation may not match '
            . 'customers’ expectations. </strong><p>Store(s) affected: testWebsiteName (testStoreName)'
            . '</p><p>Click on the link to <a href="http://example.com">ignore this notification</a></p>',
            $this->discountErrorsNotification->getText()
        );
    }
}
