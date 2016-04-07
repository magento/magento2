<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Test\Unit\Controller\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendmailTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\SendFriend\Controller\Product\Sendmail */
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

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepositoryMock;

    /** @var \Magento\Catalog\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogSessionMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactoryMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods(['getPost', 'getPostValue', 'getParam'])
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
        $this->categoryRepositoryMock = $this->getMockBuilder('Magento\Catalog\Api\CategoryRepositoryInterface')
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
        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\SendFriend\Controller\Product\Sendmail',
            [
                'request' => $this->requestMock,
                'coreRegistry' => $this->registryMock,
                'formKeyValidator' => $this->validatorMock,
                'sendFriend' => $this->sendFriendMock,
                'productRepository' => $this->productRepositoryMock,
                'categoryRepository' => $this->categoryRepositoryMock,
                'catalogSession' => $this->catalogSessionMock,
                'messageManager' => $this->messageManagerMock,
                'resultFactory' => $this->resultFactoryMock,
                'eventManager' => $this->eventManagerMock,
                'redirect' => $this->redirectMock,
                'url' => $this->urlBuilderMock,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $productId = 11;
        $categoryId = 5;
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];
        $productUrl = 'product_url';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($redirectMock);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                    ['cat_id', null, $categoryId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        /** @var \Magento\Catalog\Api\Data\CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->getMockBuilder('Magento\Catalog\Api\Data\CategoryInterface')
            ->getMockForAbstractClass();

        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($categoryId, null)
            ->willReturn($categoryMock);

        $productMock->expects($this->once())
            ->method('setCategory')
            ->with($categoryMock);

        $this->registryMock->expects($this->exactly(2))
            ->method('register')
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                    ['current_category', $categoryMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['sender', $sender],
                    ['recipients', $recipients],
                ]
            );

        $this->sendFriendMock->expects($this->once())
            ->method('setSender')
            ->with($sender)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setRecipients')
            ->with($recipients)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->sendFriendMock->expects($this->once())
            ->method('send')
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('The link to a friend was sent.'))
            ->willReturnSelf();

        $productMock->expects($this->once())
            ->method('getProductUrl')
            ->willReturn($productUrl);

        $this->redirectMock->expects($this->once())
            ->method('success')
            ->with($productUrl)
            ->willReturnArgument(0);

        $redirectMock->expects($this->once())
            ->method('setUrl')
            ->with($productUrl)
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutValidationAndCategory()
    {
        $productId = 11;
        $categoryId = 5;
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];
        $redirectUrl = 'redirect_url';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($redirectMock);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                    ['cat_id', null, $categoryId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($categoryId, null)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('No Category Exception.')));

        $productMock->expects($this->never())
            ->method('setCategory');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['sender', $sender],
                    ['recipients', $recipients],
                ]
            );

        $this->sendFriendMock->expects($this->once())
            ->method('setSender')
            ->with($sender)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setRecipients')
            ->with($recipients)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('validate')
            ->willReturn(['Some error']);
        $this->sendFriendMock->expects($this->never())
            ->method('send');

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Some error'))
            ->willReturnSelf();

        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with($formData);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sendfriend/product/send', ['_current' => true])
            ->willReturn($redirectUrl);

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($redirectUrl)
            ->willReturnArgument(0);

        $redirectMock->expects($this->once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutValidationAndCategoryWithProblems()
    {
        $productId = 11;
        $categoryId = 5;
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];
        $redirectUrl = 'redirect_url';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($redirectMock);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                    ['cat_id', null, $categoryId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($categoryId, null)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('No Category Exception.')));

        $productMock->expects($this->never())
            ->method('setCategory');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['sender', $sender],
                    ['recipients', $recipients],
                ]
            );

        $this->sendFriendMock->expects($this->once())
            ->method('setSender')
            ->with($sender)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setRecipients')
            ->with($recipients)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('validate')
            ->willReturn('Some error');
        $this->sendFriendMock->expects($this->never())
            ->method('send');

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('We found some problems with the data.'))
            ->willReturnSelf();

        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with($formData);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sendfriend/product/send', ['_current' => true])
            ->willReturn($redirectUrl);

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($redirectUrl)
            ->willReturnArgument(0);

        $redirectMock->expects($this->once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithLocalizedException()
    {
        $productId = 11;
        $categoryId = 5;
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];
        $redirectUrl = 'redirect_url';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($redirectMock);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                    ['cat_id', null, $categoryId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($categoryId, null)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('No Category Exception.')));

        $productMock->expects($this->never())
            ->method('setCategory');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['sender', $sender],
                    ['recipients', $recipients],
                ]
            );

        $this->sendFriendMock->expects($this->once())
            ->method('setSender')
            ->with($sender)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setRecipients')
            ->with($recipients)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Localized Exception.')));
        $this->sendFriendMock->expects($this->never())
            ->method('send');

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Localized Exception.'))
            ->willReturnSelf();

        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with($formData);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sendfriend/product/send', ['_current' => true])
            ->willReturn($redirectUrl);

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($redirectUrl)
            ->willReturnArgument(0);

        $redirectMock->expects($this->once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithException()
    {
        $productId = 11;
        $categoryId = 5;
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];
        $redirectUrl = 'redirect_url';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($redirectMock);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                    ['cat_id', null, $categoryId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, false)
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->categoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($categoryId, null)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('No Category Exception.')));

        $productMock->expects($this->never())
            ->method('setCategory');

        $this->registryMock->expects($this->once())
            ->method('register')
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $this->requestMock->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap(
                [
                    ['sender', $sender],
                    ['recipients', $recipients],
                ]
            );

        $this->sendFriendMock->expects($this->once())
            ->method('setSender')
            ->with($sender)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setRecipients')
            ->with($recipients)
            ->willReturnSelf();
        $this->sendFriendMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $exception = new \Exception(__('Exception.'));
        $this->sendFriendMock->expects($this->once())
            ->method('validate')
            ->willThrowException($exception);
        $this->sendFriendMock->expects($this->never())
            ->method('send');

        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($exception, __('Some emails were not sent.'))
            ->willReturnSelf();

        $this->catalogSessionMock->expects($this->once())
            ->method('setSendfriendFormData')
            ->with($formData);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sendfriend/product/send', ['_current' => true])
            ->willReturn($redirectUrl);

        $this->redirectMock->expects($this->once())
            ->method('error')
            ->with($redirectUrl)
            ->willReturnArgument(0);

        $redirectMock->expects($this->once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutProduct()
    {
        $sender = 'sender';
        $recipients = 'recipients';
        $formData = [
            'sender' => $sender,
            'recipients' => $recipients,
        ];

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject $forwardMock */
        $forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [], $redirectMock],
                    [\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD, [], $forwardMock],
                ]
            );

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($forwardMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutData()
    {
        $productId = 11;
        $formData = '';

        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject $forwardMock */
        $forwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [], $redirectMock],
                    [\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD, [], $forwardMock],
                ]
            );

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $productId],
                ]
            );

        /** @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->setMethods(['isVisibleInCatalog', 'setCategory', 'getProductUrl'])
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
            ->willReturnMap(
                [
                    ['product', $productMock, false, null],
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($formData);

        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($forwardMock, $this->model->execute());
    }

    public function testExecuteWithoutFormKey()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject $redirectMock */
        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnMap(
                [
                    [\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [], $redirectMock],
                ]
            );

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('sendfriend/product/send', ['_current' => true])
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->model->execute());
    }
}
