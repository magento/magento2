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
namespace Magento\Paypal\Block\Billing\Agreement;

/**
 * Class ViewTest
 * @package Magento\Paypal\Block\Billing\Agreement
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var View
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->orderCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->orderConfig = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);

        $this->block = $objectManager->getObject(
            'Magento\Paypal\Block\Billing\Agreement\View',
            [
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
            ]
        );
    }

    public function testGetRelatedOrders()
    {
        $visibleStatuses = [];

        $orderCollection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Collection',
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder'],
            [],
            '',
            false
        );
        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('status', ['in' => $visibleStatuses])
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(3))
            ->method('setOrder')
            ->will($this->returnValue($orderCollection));

        $this->orderCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderCollection));
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($visibleStatuses));

        $this->block->getRelatedOrders();
    }
}
