<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirect;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $reviewSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $review;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rating;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
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
        $reviewFactory = $this->createPartialMock(\Magento\Review\Model\ReviewFactory::class, ['create']);
        $reviewFactory->expects($this->once())->method('create')->willReturn($this->review);
        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, ['getCustomerId']);
        $this->rating = $this->createPartialMock(
            \Magento\Review\Model\Rating::class,
            ['setRatingId', 'setReviewId', 'setCustomerId', 'addOptionVote']
        );
        $ratingFactory = $this->createPartialMock(\Magento\Review\Model\RatingFactory::class, ['create']);
        $ratingFactory->expects($this->once())->method('create')->willReturn($this->rating);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->store = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getId', 'getWebsiteId']
        );

        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

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
                'reviewFactory' => $reviewFactory,
                'customerSession' => $this->customerSession,
                'ratingFactory' => $ratingFactory,
                'storeManager' => $storeManager,
                'context' => $this->context
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
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
