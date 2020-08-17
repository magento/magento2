<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Controller\Product\Post;
use Magento\Review\Model\Rating;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $redirect;

    /**
     * @var MockObject
     */
    protected $request;

    /**
     * @var MockObject
     */
    protected $response;

    /**
     * @var MockObject
     */
    protected $formKeyValidator;

    /**
     * @var MockObject
     */
    protected $reviewSession;

    /**
     * @var MockObject
     */
    protected $eventManager;

    /**
     * @var MockObject
     */
    protected $productRepository;

    /**
     * @var MockObject
     */
    protected $coreRegistry;

    /**
     * @var MockObject
     */
    protected $review;

    /**
     * @var MockObject
     */
    protected $customerSession;

    /**
     * @var MockObject
     */
    protected $rating;

    /**
     * @var MockObject
     */
    protected $messageManager;

    /**
     * @var MockObject
     */
    protected $store;

    /**
     * @var Post
     */
    protected $model;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->request = $this->createPartialMock(Http::class, ['getParam']);
        $this->response = $this->createPartialMock(\Magento\Framework\App\Response\Http::class, ['setRedirect']);
        $this->formKeyValidator = $this->createPartialMock(
            Validator::class,
            ['validate']
        );
        $this->reviewSession = $this->getMockBuilder(Generic::class)
            ->addMethods(['getFormData', 'getRedirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->coreRegistry = $this->createMock(Registry::class);
        $this->review = $this->getMockBuilder(Review::class)
            ->addMethods(['setEntityPkValue', 'setStatusId', 'setCustomerId', 'setStoreId', 'setStores'])
            ->onlyMethods([
                'setData',
                'validate',
                'setEntityId',
                'getEntityIdByCode',
                'save',
                'getId',
                'aggregate',
                'unsetData'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $reviewFactory = $this->createPartialMock(ReviewFactory::class, ['create']);
        $reviewFactory->expects($this->once())->method('create')->willReturn($this->review);
        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerId']);
        $this->rating = $this->getMockBuilder(Rating::class)
            ->addMethods(['setRatingId', 'setReviewId', 'setCustomerId'])
            ->onlyMethods(['addOptionVote'])
            ->disableOriginalConstructor()
            ->getMock();
        $ratingFactory = $this->createPartialMock(RatingFactory::class, ['create']);
        $ratingFactory->expects($this->once())->method('create')->willReturn($this->rating);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->store = $this->createPartialMock(
            Store::class,
            ['getId', 'getWebsiteId']
        );

        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $objectManagerHelper = new ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManager
            ]
        );
        $this->model = $objectManagerHelper->getObject(
            Post::class,
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
            Product::class,
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
            ->with(Review::ENTITY_PRODUCT_CODE)
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
            ->with(Review::STATUS_PENDING)
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
