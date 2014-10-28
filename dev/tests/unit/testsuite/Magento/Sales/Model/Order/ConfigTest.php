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
namespace Magento\Sales\Model\Order;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Sales\Model\Order\Config
     */
    protected $salesConfig;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Status\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusCollectionFactoryMock;

    public function setUp()
    {
        $orderStatusFactory = $this->getMock('Magento\Sales\Model\Order\StatusFactory', [], [], '', false, false);
        $this->orderStatusCollectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\CollectionFactory',
            ['create'],
            [],
            '',
            false,
            false
        );
        $this->salesConfig = new Config($orderStatusFactory, $this->orderStatusCollectionFactoryMock);
    }

    public function testGetInvisibleOnFrontStatuses()
    {
        $statuses = [
            new \Magento\Framework\Object(
                [
                    'status' => 'canceled',
                    'is_default' => 1,
                    'visible_on_front' => 1
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'complete',
                    'is_default' => 1,
                    'visible_on_front' => 0
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'processing',
                    'is_default' => 1,
                    'visible_on_front' => 1
                ]
            ),
            new \Magento\Framework\Object(
                [
                    'status' => 'pending_payment',
                    'is_default' => 1,
                    'visible_on_front' => 0
                ]
            ),
        ];
        $expectedResult = ['complete', 'pending_payment'];

        $collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\Collection',
            ['create', 'joinStates'],
            [],
            '',
            false,
            false
        );
        $this->orderStatusCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())
            ->method('joinStates')
            ->will($this->returnValue($statuses));

        $result = $this->salesConfig->getInvisibleOnFrontStatuses();
        $this->assertSame($expectedResult, $result);
    }
}
