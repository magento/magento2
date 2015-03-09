<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Review\Model\Review;

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

    public function setUp()
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
            ['setData', 'validate', 'setEntityId', 'getEntityIdByCode', 'setEntityPkValue', 'setStatusId',
                'setCustomerId', 'setStoreId', 'setStores', 'save', 'getId', 'aggregate'],
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
        $reviewFactory->expects($this->once())->method('create')->will($this->returnValue($this->review));
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
        $ratingFactory->expects($this->once())->method('create')->will($this->returnValue($this->rating));
        $this->messageManager = $this->getMock('\Magento\Framework\Message\ManagerInterface');

        $this->store = $this->getMock('\Magento\Store\Model\Store', ['getId'], [], '', false);
        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                '\Magento\Review\Controller\Product\Post',
                [
                    'request' => $this->request,
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
                    'messageManager' => $this->messageManager,
                ]
            );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $ratingsData = ['ratings' => [1 => 1]];
        $productId = 1;
        $customerId = 1;
        $storeId = 1;
        $reviewId = 1;
        $redirectUrl = 'url';
        $this->formKeyValidator->expects($this->any())->method('validate')
            ->with($this->request)
            ->will($this->returnValue(true));
        $this->reviewSession->expects($this->any())->method('getFormData')
            ->with(true)
            ->will($this->returnValue($ratingsData));
        $this->eventManager->expects($this->at(0))->method('dispatch')
            ->with('review_controller_product_init_before', ['controller_action' => $this->model])
            ->will($this->returnSelf());
        $this->request->expects($this->at(0))->method('getParam')
            ->with('category', false)
            ->will($this->returnValue(false));
        $this->request->expects($this->at(1))->method('getParam')
            ->with('id')
            ->will($this->returnValue(1));
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'isVisibleInCatalog', 'isVisibleInSiteVisibility', 'getId'],
            [],
            '',
            false
        );
        $product->expects($this->once())
            ->method('isVisibleInCatalog')
            ->will($this->returnValue(true));
        $product->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue(true));
        $this->productRepository->expects($this->any())->method('getById')
            ->with(1)
            ->will($this->returnValue($product));
        $this->coreRegistry->expects($this->at(0))->method('register')
            ->with('current_product', $product)
            ->will($this->returnSelf());
        $this->coreRegistry->expects($this->at(1))->method('register')
            ->with('product', $product)
            ->will($this->returnSelf());
        $this->eventManager->expects($this->at(1))->method('dispatch')
            ->with('review_controller_product_init', ['product' => $product])
            ->will($this->returnSelf());
        $this->eventManager->expects($this->at(2))->method('dispatch')
            ->with('review_controller_product_init_after', ['product' => $product, 'controller_action' => $this->model])
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('setData')
            ->with($ratingsData)
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('validate')
            ->will($this->returnValue(true));
        $this->review->expects($this->once())->method('getEntityIdByCode')
            ->with(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
            ->will($this->returnValue(1));
        $this->review->expects($this->once())->method('setEntityId')
            ->with(1)
            ->will($this->returnSelf());
        $product->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->review->expects($this->once())->method('setEntityPkValue')
            ->with($productId)
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('setStatusId')
            ->with(\Magento\Review\Model\Review::STATUS_PENDING)
            ->will($this->returnSelf());
        $this->customerSession->expects($this->exactly(2))->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $this->review->expects($this->once())->method('setCustomerId')->with($customerId)->will($this->returnSelf());
        $this->store->expects($this->exactly(2))->method('getId')
            ->will($this->returnValue($storeId));
        $this->review->expects($this->once())->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('setStores')
            ->with([$storeId])
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('save')
            ->will($this->returnSelf());
        $this->rating->expects($this->once())->method('setRatingId')
            ->with(1)
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('getId')
            ->will($this->returnValue($reviewId));
        $this->rating->expects($this->once())->method('setReviewId')
            ->with($reviewId)
            ->will($this->returnSelf());
        $this->rating->expects($this->once())->method('setCustomerId')
            ->with($customerId)
            ->will($this->returnSelf());
        $this->rating->expects($this->once())->method('addOptionVote')
            ->with(1, $productId)
            ->will($this->returnSelf());
        $this->review->expects($this->once())->method('aggregate')
            ->will($this->returnSelf());
        $this->messageManager->expects($this->once())->method('addSuccess')
            ->with('Your review has been accepted for moderation.')
            ->will($this->returnSelf());
        $this->reviewSession->expects($this->once())->method('getRedirectUrl')
            ->with(true)
            ->will($this->returnValue($redirectUrl));
        $this->response->expects($this->once())->method('setRedirect')
            ->with($redirectUrl)
            ->will($this->returnSelf());
        $this->model->execute();
    }
}
