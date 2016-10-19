<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\SalesInventory\Model\Order\Creditmemo\SimpleQtyValue;

/**
 * Class SimpleQtyValueTest
 */
class SimpleQtyValueTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SimpleQtyValue */
    private $simpleQtyValue;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoItemInterface */
    private $creditmemoItemMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoInterface */
    private $creditmemoMock;

    public function setUp()
    {
        $this->creditmemoItemMock = $this->getMockBuilder(CreditmemoItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->simpleQtyValue = new SimpleQtyValue();
    }

    public function testGet()
    {
        $this->creditmemoItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn(42);

        $this->assertEquals($this->simpleQtyValue->get(
            $this->creditmemoItemMock,
            $this->creditmemoMock,
            null,
            null
        ), 42);
    }
}
