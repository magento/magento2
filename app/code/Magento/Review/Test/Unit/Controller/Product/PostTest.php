<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Review\Model\Review;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class PostTest extends \PHPUnit_Framework_TestCase
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->redirect = $this->getMock('\Magento\Framework\App\Response\RedirectInterface');
        $this->request = $this->getMock('\Magento\Framework\App\Request\Http', ['getParam'], [], '', false);
        $this->response = $this->getMock('\Magento\Framework\App\Response\Http', ['setRedirect'], [], '', false);
        $this->formKeyValidator = $this->getMock(
            'Magento\Framework\Data\Form\FormKey\Validator',
            ['validate'],
            [],
            '',
            false
        );
        $this->reviewSession = $this->getMock(
            '\Magento\Framework\Session\Generic',
            ['getFormData', 'getRedirectUrl'],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->productRepository = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->coreRegistry = $this->getMock('\Magento\Framework\Registry');
        $this->review = $this->getMock(
            '\Magento\Review\Model\Review',
            [
                'setData', 'validate', 'setEntityId', 'getEntityIdByCode', 'setEntityPkValue', 'setStatusId',
                'setCustomerId', 'setStoreId', 'setStores', 'save', 'getId', 'aggregate', 'unsetData'
            ],
            [],
            '',
            false,
            false
        );
        $reviewFactory = $this->getMock(
            '\Magento\Review\Model\ReviewFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $reviewFactory->expects($this->once())->method('create')->willReturn($this->review);
        $this->customerSession = $this->getMock(
            '\Magento\Customer\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false,
            false
        );
        $this->rating = $this->getMock(
            '\Magento\Review\Model\Rating',
            ['setRatingId', 'setReviewId', 'setCustomerId', 'addOptionVote'],
            [],
            '',
            false,
            false
        );
        $ratingFactory = $this->getMock(
            '\Magento\Review\Model\RatingFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $ratingFactory->expects($this->once())->method('create')->willReturn($this->rating);
        $this->messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $this->store = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getId', 'getWebsiteId'],
            [],
            '',
            false
        );
        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            'Magento\Framework\App\Action\Context',
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManager
            ]
        );
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Review\Controller\Product\Post',
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
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'isVisibleInCatalog', 'isVisibleInSiteVisibility', 'getId', 'getWebsiteIds'],
            [],
            '',
            false
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
        $this->store->expects($this->once())->method('getWebsiteId')
            ->willReturn(1);
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
        $this->messageManager->expects($this->once())->method('addSuccess')
            ->with(__('You submitted your review for moderation.'))
            ->willReturnSelf();
        $this->reviewSession->expects($this->once())->method('getRedirectUrl')
            ->with(true)
            ->willReturn($redirectUrl);

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }
}
