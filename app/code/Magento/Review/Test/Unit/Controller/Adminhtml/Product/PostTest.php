<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Controller\Adminhtml\Product\Post;
use Magento\Review\Model\Rating;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

    /**
     * @var Post
     */
    protected $postController;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeModelMock;

    /**
     * @var Review|MockObject
     */
    protected $reviewMock;

    /**
     * @var ReviewFactory|MockObject
     */
    protected $reviewFactoryMock;

    /**
     * @var Rating|MockObject
     */
    protected $ratingMock;

    /**
     * @var RatingFactory|MockObject
     */
    protected $ratingFactoryMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    protected function setUp(): void
    {
        $this->_prepareMockObjects();

        $objectManagerHelper = new ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
        $this->postController = $objectManagerHelper->getObject(
            Post::class,
            [
                'reviewFactory' => $this->reviewFactoryMock,
                'ratingFactory' => $this->ratingFactoryMock,
                'context' => $this->context
            ]
        );
    }

    /**
     * Get mock objects for SetUp()
     */
    protected function _prepareMockObjects()
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeModelMock = $this->createPartialMock(Store::class, ['__wakeup', 'getId']);
        $this->reviewMock = $this->createPartialMock(
            Review::class,
            ['save', 'getId', 'aggregate', 'getEntityIdByCode']
        );
        $this->reviewFactoryMock = $this->createPartialMock(ReviewFactory::class, ['create']);
        $this->ratingMock = $this->createPartialMockWithReflection(
            Rating::class,
            ['setRatingId', 'setReviewId', 'addOptionVote']
        );
        $this->ratingFactoryMock = $this->createPartialMock(RatingFactory::class, ['create']);
        $this->ratingFactoryMock->expects($this->any())->method('create')->willReturn($this->ratingMock);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * Check postAction method and assert that review model storeId equals null.
     */
    public function testPostAction()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['product_id', false, 1],
                    ['ratings', [], ['1' => '1']]
                ]
            );
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(['status_id' => 1]);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(StoreManagerInterface::class)
            ->willReturn($this->storeManagerMock);
        $this->reviewFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->reviewMock);
        $this->ratingFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->ratingMock);
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(true);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeModelMock);
        $this->storeModelMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->reviewMock->expects($this->once())
            ->method('save')
            ->willReturn($this->reviewMock);
        $this->reviewMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->reviewMock->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->reviewMock);
        $this->reviewMock->expects($this->once())
            ->method('getEntityIdByCode')
            ->with(Review::ENTITY_PRODUCT_CODE)
            ->willReturn(1);
        $this->ratingMock->expects($this->once())
            ->method('setRatingId')
            ->willReturnSelf();
        $this->ratingMock->expects($this->once())
            ->method('setReviewId')
            ->willReturnSelf();
        $this->ratingMock->expects($this->once())
            ->method('addOptionVote')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->postController->execute());
    }
}
