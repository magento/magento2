<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Review\Helper\Data as ReviewHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reviewSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $review;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rating;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Review\Controller\Product\Post
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Review\Model\ReviewFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Review\Model\RatingFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ratingFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var ReviewHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * Setup environment
     *
     * @throws \ReflectionException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getParam']);
        $this->response = $this->createPartialMock(\Magento\Framework\App\Response\Http::class, ['setRedirect']);
        $this->formKeyValidator = $this->createPartialMock(
            \Magento\Framework\Data\Form\FormKey\Validator::class,
            ['validate']
        );
        $this->reviewSession = $this->createPartialMock(
            \Magento\Framework\Session\Generic::class,
            ['getFormData', 'getRedirectUrl']
        );
        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $this->review = $this->createPartialMock(
            \Magento\Review\Model\Review::class,
            [
                'setData',
                'validate',
                'setEntityId',
                'getEntityIdByCode',
                'setEntityPkValue',
                'setStatusId',
                'setCustomerId',
                'setStoreId',
                'setStores',
                'save',
                'getId',
                'aggregate',
                'unsetData'
            ]
        );
        $this->reviewFactory = $this->createPartialMock(\Magento\Review\Model\ReviewFactory::class, ['create']);
        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, ['getCustomerId']);
        $this->rating = $this->createPartialMock(
            \Magento\Review\Model\Rating::class,
            ['setRatingId', 'setReviewId', 'setCustomerId', 'addOptionVote']
        );
        $this->ratingFactory = $this->createPartialMock(\Magento\Review\Model\RatingFactory::class, ['create']);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->store = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getId', 'getWebsiteId']
        );

        $this->storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);

        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            \Magento\Framework\App\Action\Context::class,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManager
            ]
        );
        $this->model = $objectManagerHelper->getObject(
            \Magento\Review\Controller\Product\Post::class,
            [
                'response' => $this->response,
                'redirect' => $this->redirect,
                'formKeyValidator' => $this->formKeyValidator,
                'reviewSession' => $this->reviewSession,
                'eventManager' => $this->eventManager,
                'productRepository' => $this->productRepository,
                'coreRegistry' => $this->coreRegistry,
                'reviewFactory' => $this->reviewFactory,
                'customerSession' => $this->customerSession,
                'ratingFactory' => $this->ratingFactory,
                'storeManager' => $this->storeManager,
                'context' => $this->context
            ]
        );

        $this->initObjectManager();

        $this->helperMock = $this->createPartialMock(
            ReviewHelper::class,
            ['isEnableReview']
        );
        $this->objectManager->expects($this->once())->method('get')
            ->with(ReviewHelper::class)
            ->will($this->returnValue($this->helperMock));
    }

    /**
     * Init object manager
     *
     * @throws \ReflectionException
     */
    private function initObjectManager()
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $postController = new \ReflectionClass($this->model);
        $objectManagerProp = $postController->getProperty('_objectManager');
        $objectManagerProp->setAccessible(true);
        $objectManagerProp->setValue($this->model, $this->objectManager);
    }

    /**
     * Test execute function when review product is disabled
     */
    public function testExecuteWhenDisableReview()
    {
        $this->formKeyValidator->expects($this->any())->method('validate')
            ->with($this->request)
            ->willReturn(true);
        $this->helperMock->expects($this->once())
            ->method('isEnableReview')
            ->willReturn(false);
        $this->reviewSession->expects($this->never())->method('getFormData');
        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    /**
     * Test execute full function
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $this->reviewFactory->expects($this->once())->method('create')->willReturn($this->review);
        $this->ratingFactory->expects($this->once())->method('create')->willReturn($this->rating);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->store);
        $reviewData = [
            'ratings' => [1 => 1],
            'review_id' => 2
        ];
        $productId = 1;
        $customerId = 1;
        $storeId = 1;
        $reviewId = 1;
        $redirectUrl = 'url';
        $this->formKeyValidator->expects($this->any())->method('validate')
            ->with($this->request)
            ->willReturn(true);
        $this->helperMock->expects($this->once())
            ->method('isEnableReview')
            ->willReturn(true);
        $this->reviewSession->expects($this->any())->method('getFormData')
            ->with(true)
            ->willReturn($reviewData);
        $this->request->expects($this->at(0))->method('getParam')
            ->with('category', false)
            ->willReturn(false);
        $this->request->expects($this->at(1))->method('getParam')
            ->with('id')
            ->willReturn(1);
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'isVisibleInCatalog', 'isVisibleInSiteVisibility', 'getId', 'getWebsiteIds']
        );
        $product->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);

        $product->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([1]);

        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->productRepository->expects($this->any())->method('getById')
            ->with(1)
            ->willReturn($product);
        $this->coreRegistry->expects($this->at(0))->method('register')
            ->with('current_product', $product)
            ->willReturnSelf();
        $this->coreRegistry->expects($this->at(1))->method('register')
            ->with('product', $product)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('setData')
            ->with($reviewData)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('validate')
            ->willReturn(true);
        $this->review->expects($this->once())->method('getEntityIdByCode')
            ->with(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            ->willReturn(1);
        $this->review->expects($this->once())->method('setEntityId')
            ->with(1)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('unsetData')->with('review_id');
        $product->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($productId);
        $this->review->expects($this->once())->method('setEntityPkValue')
            ->with($productId)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('setStatusId')
            ->with(\Magento\Review\Model\Review::STATUS_PENDING)
            ->willReturnSelf();
        $this->customerSession->expects($this->exactly(2))->method('getCustomerId')
            ->willReturn($customerId);
        $this->review->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $this->store->expects($this->exactly(2))->method('getId')
            ->willReturn($storeId);
        $this->review->expects($this->once())->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('setStores')
            ->with([$storeId])
            ->willReturnSelf();
        $this->review->expects($this->once())->method('save')
            ->willReturnSelf();
        $this->rating->expects($this->once())->method('setRatingId')
            ->with(1)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('getId')
            ->willReturn($reviewId);
        $this->rating->expects($this->once())->method('setReviewId')
            ->with($reviewId)
            ->willReturnSelf();
        $this->rating->expects($this->once())->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $this->rating->expects($this->once())->method('addOptionVote')
            ->with(1, $productId)
            ->willReturnSelf();
        $this->review->expects($this->once())->method('aggregate')
            ->willReturnSelf();
        $this->messageManager->expects($this->once())->method('addSuccessMessage')
            ->with(__('You submitted your review for moderation.'))
            ->willReturnSelf();
        $this->reviewSession->expects($this->once())->method('getRedirectUrl')
            ->with(true)
            ->willReturn($redirectUrl);

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }
}
