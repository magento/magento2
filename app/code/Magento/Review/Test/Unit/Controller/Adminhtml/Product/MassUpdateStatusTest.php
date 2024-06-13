<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Controller\Adminhtml\Product;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Review\Controller\Adminhtml\Product\MassUpdateStatus;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassUpdateStatusTest extends TestCase
{
    /**
     * @var MassUpdateStatus
     */
    private $massUpdateStatus;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->collectionMock = $this->createMock(ReviewCollection::class);
        $resource = $this->createMock(ReviewResourceModel::class);
        $resource->method('getIdFieldName')
            ->willReturn('id');
        $this->collectionMock->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $contextMock->method('getRequest')
            ->willReturn($this->requestMock);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $contextMock->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->resultRedirectFactory = $this->createMock(ResultFactory::class);
        $this->redirectMock = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->method('create')->willReturn($this->redirectMock);
        $contextMock->method('getResultFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->massUpdateStatus = new MassUpdateStatus(
            $contextMock,
            $this->createMock(Registry::class),
            $this->createMock(ReviewFactory::class),
            $this->createMock(RatingFactory::class),
            $this->collectionFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->requestMock->expects(self::atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['reviews', null, [1, 2]],
                    ['status', null, Review::STATUS_APPROVED],
                    ['ret', null, 'index'],

                ]
            );
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())
            ->method('addStoreData')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.id', [1, 2])
            ->willReturnSelf();
        $modelMock = $this->getMockBuilder(Review::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStatusId'])
            ->onlyMethods(['_getResource'])
            ->getMock();
        $modelMock->expects($this->once())
            ->method('setStatusId')
            ->with(Review::STATUS_APPROVED)
            ->willReturnSelf();
        $modelMock->method('_getResource')
            ->willReturn($this->createMock(ReviewResourceModel::class));
        $this->collectionMock->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$modelMock]));
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been updated.', 2));
        $this->massUpdateStatus->execute();
    }
}
