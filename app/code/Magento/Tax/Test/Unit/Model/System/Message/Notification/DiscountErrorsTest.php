<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class DiscountErrorsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiscountErrorsNotification
     */
    private $discountErrorsNotification;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    protected function setUp()
    {
        parent::setUp();

        $websiteMock = $this->getMock(WebsiteInterface::class, [], [], '', false);
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
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->getMock(UrlInterface::class, [], [], '', false);
        $this->taxConfigMock = $this->getMock(TaxConfig::class, [], [], '', false);
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
            '<strong>Warning tax discount configuration might result in different discounts than a customer might '
            . 'expect. </strong><p>Store(s) affected: testWebsiteName (testStoreName)</p><p>Click on the link to '
            . '<a href="http://example.com">ignore this notification</a></p>',
            $this->discountErrorsNotification->getText()
        );
    }
}
