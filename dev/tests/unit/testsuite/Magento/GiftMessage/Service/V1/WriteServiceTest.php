<?php
/** 
 * 
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Service\V1;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $giftMessageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $giftMessageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
    {
        $objectManager =new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $this->giftMessageManagerMock =
            $this->getMock('\Magento\GiftMessage\Model\GiftMessageManager', [], [], '', false);
        $this->helperMock = $this->getMock('\Magento\GiftMessage\Helper\Message', [], [], '', false);
        $this->productLoaderMock =
            $this->getMock('\Magento\Catalog\Service\V1\Product\ProductLoader', [], [], '', false);
        $this->giftMessageMock = $this->getMock('\Magento\GiftMessage\Service\V1\Data\Message', [], [], '', false);
        $this->quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'getItemsCount',
                'isVirtual',
                'getBillingAddress',
                'getShippingAddress',
                'getItemById',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->billingAddressMock =
            $this->getMock('\Magento\Sales\Model\Quote\Address', ['getCountryId', '__wakeup'], [], '', false);
        $this->shippingAddressMock =
            $this->getMock('\Magento\Sales\Model\Quote\Address', ['getCountryId', '__wakeup'], [], '', false);
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);

        $this->service = $objectManager->getObject(
            'Magento\GiftMessage\Service\V1\WriteService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'giftMessageManager' => $this->giftMessageManagerMock,
                'helper' => $this->helperMock,
                'productLoader' => $this->productLoaderMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Gift Messages is not applicable for empty cart
     */
    public function testSetForQuoteWithInputException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(0));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages is not applicable for virtual products
     */
    public function testSetForQuoteWithInvalidTransitionException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }

    public function testSetForQuote()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(12));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(13));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('getIsMessagesAvailable')
            ->with('', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(true));
        $this->giftMessageMock->expects($this->once())->method('getSender')->will($this->returnValue('sender'));
        $this->giftMessageMock->expects($this->once())->method('getRecipient')->will($this->returnValue('recipient'));
        $this->giftMessageMock->expects($this->once())->method('getMessage')->will($this->returnValue('Message'));
        $message['quote'][null] =
            [
                'from' => 'sender',
                'to' => 'recipient',
                'message' => 'Message'
            ];
        $this->giftMessageManagerMock->expects($this->once())
            ->method('add')
            ->with($message, $this->quoteMock)
            ->will($this->returnValue($this->giftMessageManagerMock));

        $this->assertTrue($this->service->setForQuote($cartId, $this->giftMessageMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no product with provided  itemId: 1 in the cart
     */
    public function testSetForItemWithNoSuchEntityException()
    {
        $cartId = 665;
        $itemId = 1;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));

        $this->service->setForItem($cartId, $this->giftMessageMock, $itemId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages is not applicable for virtual products
     */
    public function testSetForItemWithInvalidTransitionException()
    {
        $cartId = 665;
        $itemId = 1;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $quoteItem = $this->getMock('\Magento\Sales\Model\Quote\Item', ['getIsVirtual', '__wakeup'], [], '', false);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($quoteItem));
        $quoteItem->expects($this->once())->method('getIsVirtual')->will($this->returnValue(1));

        $this->service->setForItem($cartId, $this->giftMessageMock, $itemId);
    }

    public function testSetForItem()
    {
        $cartId = 665;
        $itemId = 1;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $quoteItem = $this->getMock('\Magento\Sales\Model\Quote\Item', ['getIsVirtual', '__wakeup'], [], '', false);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($quoteItem));
        $quoteItem->expects($this->once())->method('getIsVirtual')->will($this->returnValue(0));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(12));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(13));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('getIsMessagesAvailable')
            ->with('items', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(true));
        $this->giftMessageMock->expects($this->once())->method('getSender')->will($this->returnValue('sender'));
        $this->giftMessageMock->expects($this->once())->method('getRecipient')->will($this->returnValue('recipient'));
        $this->giftMessageMock->expects($this->once())->method('getMessage')->will($this->returnValue('Message'));
        $message['quote_item'][1] =
            [
                'from' => 'sender',
                'to' => 'recipient',
                'message' => 'Message'
            ];
        $this->giftMessageManagerMock->expects($this->once())
            ->method('add')
            ->with($message, $this->quoteMock)
            ->will($this->returnValue($this->giftMessageManagerMock));

        $this->assertTrue($this->service->setForItem($cartId, $this->giftMessageMock, $itemId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Billing address is not set
     */
    public function testSetMessageEmptyBillingAddressException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetMessageEmptyShippingAddressException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->any())->method('getCountryId')->will($this->returnValue(12));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->any())->method('getCountryId')->will($this->returnValue(null));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Gift Message is not available
     */
    public function testSetMessageGiftMessageIsNotAvailableException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(12));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(13));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('getIsMessagesAvailable')
            ->with('', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(false));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not add gift message to shopping cart
     */
    public function testSetMessageCouldNotAddGiftMessageException()
    {
        $cartId = 665;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->billingAddressMock));
        $this->billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(12));
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->shippingAddressMock));
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(13));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('getIsMessagesAvailable')
            ->with('', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(true));
        $this->giftMessageMock->expects($this->once())->method('getSender')->will($this->returnValue('sender'));
        $this->giftMessageMock->expects($this->once())->method('getRecipient')->will($this->returnValue('recipient'));
        $this->giftMessageMock->expects($this->once())->method('getMessage')->will($this->returnValue('Message'));
        $message['quote'][null] =
            [
                'from' => 'sender',
                'to' => 'recipient',
                'message' => 'Message'
            ];
        $exception =
            new \Magento\Framework\Exception\CouldNotSaveException('Could not add gift message to shopping cart');
        $this->giftMessageManagerMock->expects($this->once())
            ->method('add')
            ->with($message, $this->quoteMock)
            ->will($this->throwException($exception));

        $this->service->setForQuote($cartId, $this->giftMessageMock);
    }
}

