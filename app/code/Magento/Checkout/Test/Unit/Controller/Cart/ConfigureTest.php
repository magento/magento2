<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Framework\Controller\ResultFactory;

/**
 * Shopping cart edit tests
 */
class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\RequestInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Checkout\Controller\Cart\Configure | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configureController;

    /**
     * @var \Magento\Framework\App\Action\Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Checkout\Model\Cart | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    public function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->responseMock = $this->getMock('Magento\Framework\App\ResponseInterface');
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->cartMock = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configureController = $objectManagerHelper->getObject(
            'Magento\Checkout\Controller\Cart\Configure',
            [
                'context' => $this->contextMock,
                'cart' => $this->cartMock
            ]
        );
    }

    /**
     * Test checks controller call product view and send parameter to it
     *
     * @return void
     */
    public function testPrepareAndRenderCall()
    {
        $quoteId = 1;
        $actualProductId = 1;
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $viewMock = $this->getMockBuilder('Magento\Catalog\Helper\Product\View')
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequestMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        //expects
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($quoteId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('product_id')
            ->willReturn($actualProductId);
        $this->cartMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $quoteItemMock->expects($this->exactly(1))->method('getBuyRequest')->willReturn($buyRequestMock);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE, [])
            ->willReturn($pageMock);
        $this->objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with('Magento\Catalog\Helper\Product\View')
            ->willReturn($viewMock);

        $viewMock->expects($this->once())->method('prepareAndRender')->with(
            $pageMock,
            $actualProductId,
            $this->configureController,
            $this->callback(
                function ($subject) use ($buyRequestMock) {
                    return $subject->getBuyRequest() === $buyRequestMock;
                }
            )
        )->willReturn($pageMock);

        $quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $quoteItemMock->expects($this->exactly(2))->method('getProduct')->willReturn($productMock);

        $productMock->expects($this->exactly(2))->method('getId')->willReturn($actualProductId);

        $this->assertSame($pageMock, $this->configureController->execute());
    }

    /**
     * Test checks controller redirect user to cart
     * if user request product id in cart edit page is not same as quota product id
     *
     * @return void
     */
    public function testRedirectWithWrongProductId()
    {
        $quotaId = 1;
        $productIdInQuota = 1;
        $productIdInRequest = null;
        $quoteItemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['id', null, $quotaId],
                ['product_id', null, $productIdInRequest]
            ]);
        $this->cartMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $quoteItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getId')->willReturn($productIdInQuota);
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->willReturn('');
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/cart', [])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
        $this->assertSame($this->resultRedirectMock, $this->configureController->execute());
    }
}
