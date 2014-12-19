<?php
/**
 * Test for \Magento\Checkout\Controller\Onepage\Index
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Controller\Onepage;

use Magento\TestFramework\Helper\ObjectManager as ObjectManager;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $onepageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Checkout\Controller\Onepage\Index
     */
    private $model;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $titleMock;

    public function setUp()
    {
        // mock objects
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->basicMock('\Magento\Framework\ObjectManagerInterface');
        $this->dataMock = $this->basicMock('Magento\Checkout\Helper\Data');
        $this->quoteMock = $this->basicMock('\Magento\Sales\Model\Quote');
        $this->contextMock = $this->basicMock('\Magento\Framework\App\Action\Context');
        $this->sessionMock = $this->basicMock('\Magento\Customer\Model\Session');
        $this->onepageMock = $this->basicMock('\Magento\Checkout\Model\Type\Onepage');
        $this->viewMock = $this->basicMock('\Magento\Framework\App\ViewInterface');
        $this->layoutMock = $this->basicMock('\Magento\Framework\View\Layout');
        $this->requestMock = $this->basicMock('\Magento\Framework\App\RequestInterface');
        $this->responseMock = $this->basicMock('\Magento\Framework\App\ResponseInterface');
        $this->redirectMock = $this->basicMock('\Magento\Framework\App\Response\RedirectInterface');
        $this->resultPageMock = $this->basicMock('\Magento\Framework\View\Result\Page');
        $this->pageConfigMock = $this->basicMock('\Magento\Framework\View\Page\Config');
        $this->titleMock = $this->basicMock('\Magento\Framework\View\Page\Title');

        // stubs
        $this->basicStub($this->onepageMock, 'getQuote')->willReturn($this->quoteMock);
        $this->basicStub($this->viewMock, 'getLayout')->willReturn($this->layoutMock);
        $this->basicStub($this->viewMock, 'getPage')->willReturn($this->resultPageMock);
        $this->basicStub($this->layoutMock, 'getBlock')
            ->willReturn($this->basicMock('Magento\Theme\Block\Html\Head'));
        $this->basicStub($this->resultPageMock, 'getConfig')->willReturn($this->pageConfigMock);
        $this->basicStub($this->pageConfigMock, 'getTitle')->willReturn($this->titleMock);
        $this->basicStub($this->titleMock, 'set')->willReturn($this->titleMock);

        // objectManagerMock
        $objectManagerReturns = [
            ['Magento\Checkout\Helper\Data', $this->dataMock],
            ['Magento\Checkout\Model\Type\Onepage', $this->onepageMock],
            ['Magento\Checkout\Model\Session', $this->basicMock('Magento\Checkout\Model\Session')],
            ['Magento\Customer\Model\Session', $this->basicMock('Magento\Customer\Model\Session')],

        ];
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($objectManagerReturns));
        $this->basicStub($this->objectManagerMock, 'create')
            ->willReturn($this->basicMock('Magento\Framework\UrlInterface'));
        // context stubs
        $this->basicStub($this->contextMock, 'getObjectManager')->willReturn($this->objectManagerMock);
        $this->basicStub($this->contextMock, 'getView')->willReturn($this->viewMock);
        $this->basicStub($this->contextMock, 'getRequest')->willReturn($this->requestMock);
        $this->basicStub($this->contextMock, 'getResponse')->willReturn($this->responseMock);
        $this->basicStub($this->contextMock, 'getMessageManager')
            ->willReturn($this->basicMock('\Magento\Framework\Message\ManagerInterface'));
        $this->basicStub($this->contextMock, 'getRedirect')->willReturn($this->redirectMock);

        // SUT
        $this->model = $this->objectManager->getObject(
            'Magento\Checkout\Controller\Onepage\Index',
            [
                'context' => $this->contextMock,
                'customerSession' => $this->sessionMock,
            ]
        );
    }

    public function testRegenerateSessionIdOnExecute()
    {
        //Stubs to control execution flow
        $this->basicStub($this->dataMock, 'canOnepageCheckout')->willReturn(true);
        $this->basicStub($this->quoteMock, 'hasItems')->willReturn(true);
        $this->basicStub($this->quoteMock, 'getHasError')->willReturn(false);
        $this->basicStub($this->quoteMock, 'validateMinimumAmount')->willReturn(true);

        //Expected outcomes
        $this->sessionMock->expects($this->once())
            ->method('regenerateId');
        $this->viewMock->expects($this->once())
            ->method('renderLayout');

        $this->model->execute();
    }

    public function testOnepageCheckoutNotAvailable()
    {
        $this->basicStub($this->dataMock, 'canOnepageCheckout')->willReturn(false);

        $expectedPath = 'checkout/cart';
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, $expectedPath, []);

        $this->model->execute();
    }

    public function testInvalidQuote()
    {
        $this->basicStub($this->quoteMock, 'hasError')->willReturn(true);

        $expectedPath = 'checkout/cart';
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, $expectedPath, []);

        $this->model->execute();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $method
     *
     * @return \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private function basicStub($mock, $method)
    {
        return $mock->expects($this->any())
                ->method($method)
                ->withAnyParameters();
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function basicMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
