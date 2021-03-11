<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Model\Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $managerInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $reviewFactory;

    protected function setUp(): void
    {
        $this->managerInterface = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->reviewFactory = $this->createPartialMock(\Magento\Review\Model\ReviewFactory::class, ['create']);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            \Magento\Review\Model\Rss::class,
            [
                'eventManager' => $this->managerInterface,
                'reviewFactory' => $this->reviewFactory
            ]
        );
    }

    public function testGetProductCollection()
    {
        $reviewModel = $this->createPartialMock(\Magento\Review\Model\Review::class, [
                '__wakeUp',
                'getProductCollection'
            ]);
        $productCollection = $this->createPartialMock(
            \Magento\Review\Model\ResourceModel\Review\Product\Collection::class,
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
