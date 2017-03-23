<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reviewFactory;

    protected function setUp()
    {
        $this->managerInterface = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->reviewFactory = $this->getMock(\Magento\Review\Model\ReviewFactory::class, ['create'], [], '', false);

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
        $reviewModel = $this->getMock(
            \Magento\Review\Model\Review::class,
            [
                '__wakeUp',
                'getProductCollection'
            ],
            [],
            '',
            false
        );
        $productCollection = $this->getMock(
            \Magento\Review\Model\ResourceModel\Review\Product\Collection::class,
            [
                'addStatusFilter',
                'addAttributeToSelect',
                'setDateOrder'
            ],
            [],
            '',
            false
        );
        $reviewModel->expects($this->once())->method('getProductCollection')
            ->will($this->returnValue($productCollection));
        $this->reviewFactory->expects($this->once())->method('create')->will($this->returnValue($reviewModel));
        $productCollection->expects($this->once())->method('addStatusFilter')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('setDateOrder')->will($this->returnSelf());
        $this->managerInterface->expects($this->once())->method('dispatch')->will($this->returnSelf());
        $this->assertEquals($productCollection, $this->rss->getProductCollection());
    }
}
