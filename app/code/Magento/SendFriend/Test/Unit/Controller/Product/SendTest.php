<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Test\Unit\Controller\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\SendFriend\Controller\Product\Send */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorMock;

    /** @var \Magento\SendFriend\Model\SendFriend|\PHPUnit_Framework_MockObject_MockObject */
    protected $sendFriendMock;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepositoryMock;

    /** @var \Magento\Catalog\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogSessionMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactoryMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sendFriendMock = $this->getMockBuilder('Magento\SendFriend\Model\SendFriend')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder('Magento\Catalog\Api\ProductRepositoryInterface')
            ->getMockForAbstractClass();
        $this->catalogSessionMock = $this->getMockBuilder('Magento\Catalog\Model\Session')
            ->setMethods(['getSendfriendFormData', 'setSendfriendFormData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\SendFriend\Controller\Product\Send',
            [
                'request' => $this->requestMock,
                'coreRegistry' => $this->registryMock,
                'formKeyValidator' => $this->validatorMock,
                'sendFriend' => $this->sendFriendMock,
                'productRepository' => $this->productRepositoryMock,
                'catalogSession' => $this->catalogSessionMock,
                'messageManager' => $this->messageManagerMock,
                'resultFactory' => $this->resultFactoryMock,
                'eventManager' => $this->eventManagerMock,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $productId = 11;
        $formData = ['some' => 'data'];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($productId);

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $productMock, false);

        $this->sendFriendMock->expects($this->once())
            ->method('getMaxSendsToFriend')
            ->willReturn(11);
        $this->sendFriendMock->expects($this->once())
            ->method('isExceedLimit')
            ->willReturn(false);

        $this->messageManagerMock->expects($this->never())
            ->method('addNotice');

        /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock */
        $pageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE, [])
            ->willReturn($pageMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sendfriend_product', ['product' => $productMock]);

        $this->catalogSessionMock->expects($this->once())
            ->method('getSendfriendFormData')
            ->willReturn($formData);
        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with(true);

        /** @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject $layoutMock */
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $pageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        /** @var \Magento\SendFriend\Block\Send|\PHPUnit_Framework_MockObject_MockObject $blockMock */
        $blockMock = $this->getMockBuilder('Magento\SendFriend\Block\Send')
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('sendfriend.send')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('setFormData')
            ->with($formData)
            ->willReturnSelf();

        $this->assertEquals($pageMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutBlock()
    {
        $productId = 11;
        $formData = ['some' => 'data'];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($productId);

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $productMock, false);

        $this->sendFriendMock->expects($this->once())
            ->method('getMaxSendsToFriend')
            ->willReturn(11);
        $this->sendFriendMock->expects($this->once())
            ->method('isExceedLimit')
            ->willReturn(false);

        $this->messageManagerMock->expects($this->never())
            ->method('addNotice');

        /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock */
        $pageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE, [])
            ->willReturn($pageMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sendfriend_product', ['product' => $productMock]);

        $this->catalogSessionMock->expects($this->once())
            ->method('getSendfriendFormData')
            ->willReturn($formData);
        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with(true);

        /** @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject $layoutMock */
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $pageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('sendfriend.send')
            ->willReturn(false);

        $this->assertEquals($pageMock, $this->model->execute());
    }

    public function testExecuteWithNoticeAndNoData()
    {
        $productId = 11;
        $formData = null;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($productId);

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $productMock, false);

        $this->sendFriendMock->expects($this->exactly(2))
            ->method('getMaxSendsToFriend')
            ->willReturn(11);
        $this->sendFriendMock->expects($this->once())
            ->method('isExceedLimit')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addNotice')
            ->with(__('You can\'t send messages more than %1 times an hour.', 11))
            ->willReturnSelf();

        /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock */
        $pageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE, [])
            ->willReturn($pageMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('sendfriend_product', ['product' => $productMock]);

        $this->catalogSessionMock->expects($this->once())
            ->method('getSendfriendFormData')
            ->willReturn($formData);
        $this->catalogSessionMock->expects($this->never())
            ->method('setSendfriendFormData');

        $pageMock->expects($this->never())
            ->method('getLayout');

        $this->assertEquals($pageMock, $this->model->execute());
    }

    public function testExecuteWithoutParam()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn(null);

        /** @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject $forwardMock */
        $forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD, [])
            ->willReturn($forwardMock);

        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($forwardMock, $this->model->execute());
    }

    public function testExecuteWithoutProduct()
    {
        $productId = 11;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($productId);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('No Product Exception.')));

        /** @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject $forwardMock */
        $forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD, [])
            ->willReturn($forwardMock);

        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($forwardMock, $this->model->execute());
    }

    public function testExecuteWithNonVisibleProduct()
    {
        $productId = 11;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($productId);

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(false);

        /** @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject $forwardMock */
        $forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD, [])
            ->willReturn($forwardMock);

        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($forwardMock, $this->model->execute());
    }
}
