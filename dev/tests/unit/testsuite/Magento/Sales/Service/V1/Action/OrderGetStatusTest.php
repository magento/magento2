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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Model\OrderRepository;

/**
 * Class OrderGetStatusTest
 * @package Magento\Sales\Service\V1
 */
class OrderGetStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderGetStatus
     */
    protected $service;

    /**
     * @var OrderRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMock(
            'Magento\Sales\Model\OrderRepository',
            ['get'],
            [],
            '',
            false
        );

        $this->service = new OrderGetStatus($this->orderRepositoryMock);
    }

    public function testInvoke()
    {
        $status = 'pending';
        $id = 1;
        $orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getStatus'],
            [],
            '',
            false
        );
        $orderMock->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($status));
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo($id))
            ->will($this->returnValue($orderMock));
        $this->assertEquals($status, $this->service->invoke($id));
    }
}
