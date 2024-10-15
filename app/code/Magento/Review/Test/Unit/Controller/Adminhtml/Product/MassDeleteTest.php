<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Review\Controller\Adminhtml\Product\MassDelete;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\ReviewFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassDeleteTest extends TestCase
{
    /**
     * @var MassDelete
     */
    private $massDelete;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ReviewCollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $contextMock->method('getRequest')
            ->willReturn($this->requestMock);
        $messageManagerMock = $this->createMock(ManagerInterface::class);
        $contextMock->method('getMessageManager')
            ->willReturn($messageManagerMock);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $contextMock->method('getResultFactory')
            ->willReturn($resultFactoryMock);
        $resultMock = $this->createMock(Redirect::class);
        $resultFactoryMock->method('create')
            ->willReturn($resultMock);

        $coreRegistryMock = $this->createMock(Registry::class);
        $reviewFactoryMock = $this->createMock(ReviewFactory::class);
        $ratingFactoryMock = $this->createMock(RatingFactory::class);
        $this->collectionFactoryMock = $this->createMock(ReviewCollectionFactory::class);

        $this->massDelete = new MassDelete(
            $contextMock,
            $coreRegistryMock,
            $reviewFactoryMock,
            $ratingFactoryMock,
            $this->collectionFactoryMock
        );
    }

    public function testExecute(): void
    {
        $this->requestMock->expects(self::atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['reviews', null, [10, 20]],
                    ['ret', 'index', 'index'],
                ]
            );

        $collectionMock = $this->createMock(ReviewCollection::class);
        $this->collectionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($collectionMock);
        $resource = $this->createMock(ReviewResourceModel::class);
        $collectionMock->method('getResource')
            ->willReturn($resource);
        $resource->method('getIdFieldName')
            ->willReturn('id');
        $collectionMock->expects(self::once())
            ->method('addFieldToFilter')
            ->with('main_table.id', [10, 20])
            ->willReturnSelf();
        $collectionMock->expects(self::once())
            ->method('addStoreData')
            ->willReturnSelf();

        $this->massDelete->execute();
    }
}
