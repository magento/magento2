<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Observer;

use Magento\Braintree\Block\PayPal\Shortcut;
use Magento\Braintree\Observer\AddPaypalShortcuts;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddPaypalShortcutsTest
 */
class AddPaypalShortcutsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Observer\AddPaypalShortcuts
     */
    protected $addPaypalShortcutsObserver;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\PaymentMethod\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalMethodMock;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalConfigMock;

    protected function setUp()
    {
        $this->paypalMethodMock = $this->getMockBuilder('\Magento\Braintree\Model\PaymentMethod\PayPal')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paypalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->addPaypalShortcutsObserver = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Observer\AddPaypalShortcuts',
            [
                'methodPayPal' => $this->paypalMethodMock,
                'paypalConfig' => $this->paypalConfigMock,
            ]
        );
    }

    /**
     * @covers \Magento\Braintree\Observer\AddPaypalShortcuts::execute()
     */
    public function testAddPaypalShortcuts()
    {
        $orPosition = 'before';
        $isInMiniCart = true;

        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\DataObject(
            [
                'is_catalog_product' => false,
                'container' => $containerMock,
                'or_position' => $orPosition,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(true);

        $shortcutMock = $this->getMockBuilder('\Magento\Braintree\Block\PayPal\Shortcut')
            ->disableOriginalConstructor()
            ->setMethods(['setShowOrPosition', 'skipShortcutForGuest'])
            ->getMock();
        $shortcutMock->expects($this->once())
            ->method('skipShortcutForGuest')
            ->willReturn(false);
        $shortcutMock->expects($this->once())
            ->method('setShowOrPosition')
            ->with($orPosition);

        $layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface');
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                AddPaypalShortcuts::PAYPAL_SHORTCUT_BLOCK,
                '',
                [
                    'data' => [
                        Shortcut::MINI_CART_FLAG_KEY => $isInMiniCart,
                    ]
                ]
            )->willReturn($shortcutMock);

        $containerMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $containerMock->expects($this->once())
            ->method('addShortcut')
            ->with($shortcutMock);

        $this->addPaypalShortcutsObserver->execute($observer);
    }

    /**
     * @covers \Magento\Braintree\Observer\AddPaypalShortcuts::execute()
     */
    public function testAddPaypalShortcutsWithoutMinicart()
    {
        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\DataObject(
            [
                'is_catalog_product' => true,
                'container' => $containerMock
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );
        $this->paypalMethodMock->expects(static::once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects(static::once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(true);

        $containerMock->expects(static::never())
            ->method('getLayout');

        $this->addPaypalShortcutsObserver->execute($observer);
    }

    public function testAddPaypalShortcutsNotActive()
    {
        $event = new \Magento\Framework\DataObject(
            [
                'is_catalog_product' => false,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(false);
        $this->paypalConfigMock->expects($this->never())
            ->method('isShortcutCheckoutEnabled');

        $this->addPaypalShortcutsObserver->execute($observer);
    }

    public function testAddPaypalShortcutsNotEnabled()
    {
        $orPosition = 'before';

        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\DataObject(
            [
                'is_catalog_product' => false,
                'container' => $containerMock,
                'or_position' => $orPosition,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(false);

        $containerMock->expects($this->never())
            ->method('getLayout');

        $this->addPaypalShortcutsObserver->execute($observer);
    }
}
