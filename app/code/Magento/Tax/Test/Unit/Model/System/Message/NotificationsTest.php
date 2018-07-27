<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\System\Message;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notifications;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Tax\Model\System\Message\NotificationInterface;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notifications
 */
class NotificationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Notifications
     */
    private $notifications;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * @var NotificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationMock;

    protected function setUp()
    {
        parent::setUp();

        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class, [], [], '', false);
        $this->urlBuilderMock = $this->getMock(UrlInterface::class, [], [], '', false);
        $this->taxConfigMock = $this->getMock(TaxConfig::class, [], [], '', false);
        $this->notificationMock = $this->getMock(NotificationInterface::class, [], [], '', false);
        $this->notifications = (new ObjectManager($this))->getObject(
            Notifications::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
                'notifications' => [$this->notificationMock]
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
    public function dataProviderIsDisplayed()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testGetText()
    {
        $this->notificationMock->expects($this->once())->method('getText')->willReturn('Notification Text.');
        $this->taxConfigMock->expects($this->once())->method('getInfoUrl')->willReturn('http://info-url');
        $this->urlBuilderMock->expects($this->once())->method('getUrl')
            ->with('adminhtml/system_config/edit/section/tax')->willReturn('http://tax-config-url');

        $this->assertEquals(
            'Notification Text.<p>Please see <a href="http://info-url">documentation</a> for more details. '
            . 'Click here to go to <a href="http://tax-config-url">Tax Configuration</a> and change your settings.</p>',
            $this->notifications->getText()
        );
    }
}
