<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Controller\Adminhtml\Product\Post
     */
    protected $postController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeModelMock;

    /**
     * @var \Magento\Review\Model\Review|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reviewMock;

    /**
     * @var \Magento\Review\Model\ReviewFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reviewFactoryMock;

    /**
     * @var \Magento\Review\Model\Rating|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ratingMock;

    /**
     * @var \Magento\Review\Model\RatingFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ratingFactoryMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    protected function setUp(): void
    {
        $this->_prepareMockObjects();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
        $this->postController = $objectManagerHelper->getObject(
            \Magento\Review\Controller\Adminhtml\Product\Post::class,
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
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeModelMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['__wakeup', 'getId']);
        $this->reviewMock = $this->createPartialMock(
            \Magento\Review\Model\Review::class,
            ['__wakeup', 'create', 'save', 'getId', 'getResource', 'aggregate', 'getEntityIdByCode']
        );
        $this->reviewFactoryMock = $this->createPartialMock(\Magento\Review\Model\ReviewFactory::class, ['create']);
        $this->ratingMock = $this->createPartialMock(
            \Magento\Review\Model\Rating::class,
            ['__wakeup', 'setRatingId', 'setReviewId', 'addOptionVote']
        );
        $this->ratingFactoryMock = $this->createPartialMock(\Magento\Review\Model\RatingFactory::class, ['create']);
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->with(\Magento\Store\Model\StoreManagerInterface::class)
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
