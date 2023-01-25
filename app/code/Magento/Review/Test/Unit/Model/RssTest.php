<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Rss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RssTest extends TestCase
{
    /**
     * @var Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $managerInterface;

    /**
     * @var MockObject
     */
    protected $reviewFactory;

    protected function setUp(): void
    {
        $this->managerInterface = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->reviewFactory = $this->createPartialMock(ReviewFactory::class, ['create']);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            Rss::class,
            [
                'eventManager' => $this->managerInterface,
                'reviewFactory' => $this->reviewFactory
            ]
        );
    }

    public function testGetProductCollection()
    {
        $reviewModel = $this->createPartialMock(Review::class, [
            '__wakeUp',
            'getProductCollection'
        ]);
        $productCollection = $this->createPartialMock(
            Collection::class,
            [
                'addStatusFilter',
                'addAttributeToSelect',
                'setDateOrder'
            ]
        );
        $reviewModel->expects($this->once())->method('getProductCollection')
            ->willReturn($productCollection);
        $this->reviewFactory->expects($this->once())->method('create')->willReturn($reviewModel);
        $productCollection->expects($this->once())->method('addStatusFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('setDateOrder')->willReturnSelf();
        $this->managerInterface->expects($this->once())->method('dispatch')->willReturnSelf();
        $this->assertEquals($productCollection, $this->rss->getProductCollection());
    }
}
