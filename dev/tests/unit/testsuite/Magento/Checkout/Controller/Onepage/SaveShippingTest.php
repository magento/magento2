<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

/**
 * Class SaveShippingTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveShipping
     */
    protected $controller;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Sales\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $onePage;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\View | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var \Magento\Framework\App\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Core\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactory;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->coreHelper = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', ['getPost', 'isPost'], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->view = $this->getMock('Magento\Framework\App\View', [], [], '', false);
        $this->quote = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['__wakeup', 'getHasError', 'hasItems', 'validateMinimumAmount', 'isVirtual', 'getStoreId'],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->onePage = $this->getMock('Magento\Checkout\Model\Type\Onepage', [], [], '', false);

        $this->response->expects($this->any())
            ->method('setHeader')
            ->will($this->returnSelf());
        $this->onePage->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        //Object Manager mock initialization
        $valueMap = [
            ['Magento\Checkout\Model\Type\Onepage', $this->onePage],
            ['Magento\Checkout\Model\Session', $this->checkoutSession],
            ['Magento\Core\Helper\Data', $this->coreHelper],
        ];
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        $this->layoutFactory = $this->getMock('Magento\Framework\View\LayoutFactory', ['create'], [], '', false);

        //Context mock initialization
        $context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $context->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManager));
        $context->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller = $objectManager->getObject(
            'Magento\Checkout\Controller\Onepage\SaveShipping',
            [
                'context' => $context,
                'scopeConfig' => $this->scopeConfig,
                'layoutFactory' => $this->layoutFactory
            ]
        );
    }

    public function testExecute()
    {
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);
        $this->controller->execute();
    }

    public function testValidateMinimumAmount()
    {
        $expectedResult = [
            'goto_section' => 'shipping_method',
            'update_section' => [
                'name' => 'shipping-method',
                'html' => null,
            ],
            'update_progress' => [
                'html' => 'some_html',
            ],
        ];
        $this->quote->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quote->expects($this->once())
            ->method('getHasError')
            ->willReturn(false);
        $this->quote->expects($this->exactly(2))
            ->method('validateMinimumAmount')
            ->willReturn(true);

        $data = ['use_for_shipping' => 1];
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturn($data);

        $this->coreHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($expectedResult);
        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getUpdate', 'generateXml', 'generateElements', 'getOutput', 'getBlock'],
            [],
            '',
            false
        );
        $this->layoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($layout);

        $block = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->setMethods(['setAttribute', 'toHtml'])
            ->getMockForAbstractClass();
        $block->expects($this->any())
            ->method('setAttribute')
            ->willReturnSelf();
        $block->expects($this->any())
            ->method('toHtml')
            ->willReturn('some_html');

        $update = $this->getMock('Magento\Core\Model\Layout\Merge', [], [], '', false);
        $layout->expects($this->any())
            ->method('getUpdate')
            ->willReturn($update);
        $update->expects($this->any())
            ->method('load');
        $this->view->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $layout->expects($this->any())
            ->method('getBlock')
            ->willReturn($block);

        $this->controller->execute();
    }

    public function testValidateMinimumAmountNegative()
    {
        $errorMessage = 'error_message';
        $expectedResult = [
            'error' => -1,
            'message' => $errorMessage,
        ];

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn($errorMessage);
        $this->quote->expects($this->at(0))
            ->method('hasItems')
            ->willReturn(true);
        $this->quote->expects($this->at(1))
            ->method('getHasError')
            ->willReturn(false);
        $this->quote->expects($this->at(2))
            ->method('validateMinimumAmount')
            ->willReturn(true);
        $this->quote->expects($this->at(3))
            ->method('validateMinimumAmount')
            ->willReturn(false);

        $data = ['use_for_shipping' => 1];
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturn($data);

        $this->coreHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($expectedResult);

        $this->controller->execute();
    }
}
