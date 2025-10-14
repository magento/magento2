<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\System\Message;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\NotificationInterface;
use Magento\Tax\Model\System\Message\Notifications;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notifications.
 */
class NotificationsTest extends TestCase
{
    /**
     * @var Notifications
     */
    private $notifications;

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

    /**
     * @var NotificationInterface|MockObject
     */
    private $notificationMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->notificationMock = $this->getMockForAbstractClass(NotificationInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->notifications = (new ObjectManager($this))->getObject(
            Notifications::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
                'notifications' => [$this->notificationMock],
                'escaper' => $this->escaperMock,
            ]
        );
    }

    /**
     * @dataProvider dataProviderIsDisplayed
     */
    public function testIsDisplayed(
        $isNotificationDisplayed,
        $expectedResult
    ) {
        $this->notificationMock->expects($this->once())->method('isDisplayed')->willReturn($isNotificationDisplayed);
        $this->assertEquals($expectedResult, $this->notifications->isDisplayed());
    }

    /**
     * @return array
     */
    public static function dataProviderIsDisplayed()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * Unit test for getText method.
     *
     * @return void
     */
    public function testGetText()
    {
        $url = 'http://info-url';
        $this->notificationMock->expects($this->once())->method('getText')->willReturn('Notification Text.');
        $this->taxConfigMock->expects($this->once())->method('getInfoUrl')->willReturn($url);
        $this->urlBuilderMock->expects($this->once())->method('getUrl')
            ->with('adminhtml/system_config/edit/section/tax')->willReturn('http://tax-config-url');
        $this->escaperMock->expects($this->once())
            ->method('escapeUrl')
            ->with($url)
            ->willReturn($url);

        $this->assertEquals(
            'Notification Text.<p>Please see <a href="http://info-url">documentation</a> for more details. '
            . 'Click here to go to <a href="http://tax-config-url">Tax Configuration</a> and change your settings.</p>',
            $this->notifications->getText()
        );
    }

    /**
     * Unit test for getInfoUrl method.
     *
     * @return void
     */
    public function testGetInfoUrl()
    {
        $url = 'http://info-url';
        $this->taxConfigMock->expects($this->once())->method('getInfoUrl')->willReturn($url);
        $this->escaperMock->expects($this->once())
            ->method('escapeUrl')
            ->with($url)
            ->willReturn($url);

        $this->assertEquals($url, $this->notifications->getInfoUrl());
    }
}
