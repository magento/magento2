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
namespace Magento\Sales\Service\V1;

/**
 * Class OrderReadTest
 */
class OrderReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\OrderGet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderGetMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\OrderList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderListMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\OrderCommentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCommentsListMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\OrderGetStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderGetStatusMock;

    /**
     * @var \Magento\Sales\Service\V1\OrderRead
     */
    protected $orderRead;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->orderGetMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\OrderGet',
            ['invoke'],
            [],
            '',
            false
        );
        $this->orderListMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\OrderList',
            ['invoke'],
            [],
            '',
            false
        );
        $this->orderCommentsListMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\OrderCommentsList',
            ['invoke'],
            [],
            '',
            false
        );
        $this->orderGetStatusMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\OrderGetStatus',
            ['invoke'],
            [],
            '',
            false
        );

        $this->orderRead = new OrderRead(
            $this->orderGetMock,
            $this->orderListMock,
            $this->orderCommentsListMock,
            $this->orderGetStatusMock
        );
    }

    /**
     * test order get
     */
    public function testGet()
    {
        $this->orderGetMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('order-do'));
        $this->assertEquals('order-do', $this->orderRead->get(1));
    }

    /**
     * test order list
     */
    public function testSearch()
    {
        $searchCriteria = $this->getMock('Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false);
        $this->orderListMock->expects($this->once())
            ->method('invoke')
            ->with($searchCriteria)
            ->will($this->returnValue('search_result'));
        $this->assertEquals('search_result', $this->orderRead->search($searchCriteria));
    }

    /**
     * test order comments list
     */
    public function testCommentsList()
    {
        $this->orderCommentsListMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('search_result'));
        $this->assertEquals('search_result', $this->orderRead->commentsList(1));
    }

    /**
     * test order get status
     */
    public function testGetStatus()
    {
        $this->orderGetStatusMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('search_result'));
        $this->assertEquals('search_result', $this->orderRead->getStatus(1));
    }
}
