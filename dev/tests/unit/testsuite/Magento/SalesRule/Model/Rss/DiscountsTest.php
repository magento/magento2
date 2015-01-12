<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rss;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    protected function setUp()
    {
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime');
        $this->collectionFactory = $this->getMock(
            'Magento\SalesRule\Model\Resource\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->discounts = $this->objectManagerHelper->getObject(
            'Magento\SalesRule\Model\Rss\Discounts',
            [
                'dateTime' => $this->dateTime,
                'collectionFactory' => $this->collectionFactory
            ]
        );
    }

    public function testGetDiscountCollection()
    {
        $ruleCollection = $this->getMock(
            'Magento\SalesRule\Model\Resource\Rule\Collection',
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
        $this->dateTime->expects($this->once())->method('now');
        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($ruleCollection));
        $ruleCollection->expects($this->once())->method('addWebsiteGroupDateFilter')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('setOrder')->will($this->returnSelf());
        $ruleCollection->expects($this->once())->method('load')->will($this->returnSelf());
        $this->assertEquals($ruleCollection, $this->discounts->getDiscountCollection(1, 1));
    }
}
