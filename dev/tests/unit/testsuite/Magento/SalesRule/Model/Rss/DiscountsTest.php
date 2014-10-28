<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Rss;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
            [
                'create'
            ]
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
