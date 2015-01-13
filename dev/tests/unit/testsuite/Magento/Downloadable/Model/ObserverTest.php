<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model;

use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var Observer */
    private $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\Data
     */
    private $coreData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\Config
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    private $purchasedFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Downloadable\Model\Link\Purchased\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CollectionFactory
     */
    private $itemsFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object\Copy
     */
    private $objectCopyService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Object
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event
     */
    private $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\Observer
     */
    private $observerMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->coreData = $this->getMockBuilder('\Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag'])
            ->getMock();

        $this->purchasedFactory = $this->getMockBuilder('\Magento\Downloadable\Model\Link\PurchasedFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder('\Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactory = $this->getMockBuilder('\Magento\Downloadable\Model\Link\Purchased\ItemFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemsFactory = $this->getMockBuilder(
            '\Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectCopyService = $this->getMockBuilder('\Magento\Framework\Object\Copy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder('\Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['setIsAllowed'])
            ->getMock();

        $this->storeMock = $this->getMockBuilder('\Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder('\Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getResult', 'getQuote'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder('\Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->observer = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Downloadable\Model\Observer',
            [
                'coreData'          => $this->coreData,
                'scopeConfig'       => $this->scopeConfig,
                'purchasedFactory'  => $this->purchasedFactory,
                'productFactory'    => $this->productFactory,
                'itemFactory'       => $this->itemFactory,
                'checkoutSession'   => $this->checkoutSession,
                'itemsFactory'      => $this->itemsFactory,
                'objectCopyService' => $this->objectCopyService
            ]
        );
    }

    /**
     *
     * @dataProvider dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue
     *
     * @param $productType
     * @param $isAllowed
     */
    public function testIsAllowedGuestCheckoutConfigSetToTrue($productType, $isAllowed)
    {
        $this->resultMock->expects($this->at(0))
            ->method('setIsAllowed')
            ->with(true);

        if ($isAllowed) {
            $this->resultMock->expects($this->at(1))
                ->method('setIsAllowed')
                ->with(false);
        }

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMock();

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $item = $this->getMockBuilder('\Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $item->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $quote = $this->getMockBuilder('\Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems'])
            ->getMock();

        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$item]);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($this->resultMock));

        $this->eventMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quote));

        $this->scopeConfig->expects($this->exactly(1))
            ->method('isSetFlag')
            ->with(Observer::XML_PATH_DISABLE_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE, $this->storeMock)
            ->willReturn(true);

        $this->observerMock->expects($this->exactly(3))
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

        $this->assertInstanceOf(
            '\Magento\Downloadable\Model\Observer',
            $this->observer->isAllowedGuestCheckout($this->observerMock)
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestisAllowedGuestCheckoutConfigSetToTrue()
    {
        return [
            1 => [Type::TYPE_DOWNLOADABLE, true],
            2 => ['unknown', false],
        ];
    }

    /**
     *
     */
    public function testIsAllowedGuestCheckoutConfigSetToFalse()
    {
        $this->resultMock->expects($this->once())
            ->method('setIsAllowed')
            ->with(true);

        $this->eventMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->eventMock->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($this->resultMock));

        $this->scopeConfig->expects($this->exactly(1))
            ->method('isSetFlag')
            ->with(Observer::XML_PATH_DISABLE_GUEST_CHECKOUT, ScopeInterface::SCOPE_STORE, $this->storeMock)
            ->willReturn(false);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));

        $this->assertInstanceOf(
            '\Magento\Downloadable\Model\Observer',
            $this->observer->isAllowedGuestCheckout($this->observerMock)
        );
    }
}
