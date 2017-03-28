<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DiscountsTest
 * @package Magento\SalesRule\Model\Rss
 */
class DiscountsTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    protected function setUp()
    {
        $this->collectionFactory = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
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
        $ruleCollection = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Rule\Collection::class,
            [
                'addWebsiteGroupDateFilter',
                'addFieldToFilter',
                'setOrder',
                'load'
            ],
            [],
            '',
            false
        );
        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($ruleCollection));
        $ruleCollection->expects($this->once())->method('addWebsiteGroupDateFilter')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('setOrder')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('load')->will($this->returnSelf());
        $this->assertEquals($ruleCollection, $this->discounts->getDiscountCollection(1, 1));
    }
}
