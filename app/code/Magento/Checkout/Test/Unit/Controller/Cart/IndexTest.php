<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\Index;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Index
     */
    protected $controller;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Quote\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cart;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->quote = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $context->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManager);
        $context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->cart = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManagerHelper->getObject(
            'Magento\Checkout\Controller\Cart\Index',
            [
                'context' => $context,
                'checkoutSession' => $this->checkoutSession,
                'cart' => $this->cart,
                'scopeConfig' => $this->scopeConfig,
                'resultPageFactory' => $this->resultPageFactory
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithMessages()
    {
        $title = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $title->expects($this->once())
            ->method('set')
            ->with('Shopping Cart');

        $config = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);

        $page = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $page->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($page);
        $result = $this->controller->execute();
        $this->assertInstanceOf('Magento\Framework\View\Result\Page', $result);
    }
}
