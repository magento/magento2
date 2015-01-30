<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

/**
 * Shopping cart edit tests
 */
class ConfigureTest extends \Magento\TestFramework\AbstractControllerTest
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

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
     * @var \Magento\Framework\App\Response\RedirectInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

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
        $viewMock = $this->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $contextData = [
            'objectManager' => $this->objectManagerMock,
            'request' => $this->requestMock,
            'response' => $this->responseMock,
            'messageManager' => $this->messageManagerMock,
            'redirect' => $this->redirectMock,
            'view' => $viewMock
        ];

        $this->setUpContext($contextData);

        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $session = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager = $this->getMockBuilder('Magento\Framework\Store\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $formKeyValidator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartMock = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configureController = new \Magento\Checkout\Controller\Cart\Configure(
            $this->contextMock,
            $scopeConfig,
            $session,
            $storeManager,
            $formKeyValidator,
            $this->cartMock,
            $this->resultPageFactoryMock
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
        $buyRequestMock = $this->getMockBuilder('Magento\Framework\Object')
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

        $this->resultPageFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);
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
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($quotaId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('product_id')
            ->willReturn($productIdInRequest);
        $this->cartMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getItemById')->willReturn($quoteItemMock);
        $quoteItemMock->expects($this->exactly(1))->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->exactly(1))->method('getId')->willReturn($productIdInQuota);
        $this->messageManagerMock->expects($this->once())->method('addError');
        $this->redirectMock->expects($this->once())->method('redirect')->with($this->responseMock, 'checkout/cart', []);
        $this->configureController->execute();
    }
}
