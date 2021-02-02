<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DiscountsTest
 * @package Magento\SalesRule\Model\Rss
 */
class DiscountsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rss\Discounts
     */
    protected $discounts;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionFactory;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->discounts = $this->objectManagerHelper->getObject(
            \Magento\SalesRule\Model\Rss\Discounts::class,
            [
                'collectionFactory' => $this->collectionFactory
            ]
        );
    }

    public function testGetDiscountCollection()
    {
        $ruleCollection = $this->createPartialMock(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class, [
                'addWebsiteGroupDateFilter',
                'addFieldToFilter',
                'setOrder',
                'load'
            ]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())->method('addWebsiteGroupDateFilter')->willReturnSelf();
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $ruleCollection->expects($this->once())->method('setOrder')->willReturnSelf();
        $ruleCollection->expects($this->once())->method('load')->willReturnSelf();
        $this->assertEquals($ruleCollection, $this->discounts->getDiscountCollection(1, 1));
    }
}
