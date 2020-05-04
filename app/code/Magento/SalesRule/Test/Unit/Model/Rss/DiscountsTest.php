<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rss\Discounts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscountsTest extends TestCase
{
    /**
     * @var Discounts
     */
    protected $discounts;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $collectionFactory;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->discounts = $this->objectManagerHelper->getObject(
            Discounts::class,
            [
                'collectionFactory' => $this->collectionFactory
            ]
        );
    }

    public function testGetDiscountCollection()
    {
        $ruleCollection = $this->createPartialMock(Collection::class, [
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
