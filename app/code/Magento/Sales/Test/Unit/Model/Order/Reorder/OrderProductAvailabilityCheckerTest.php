<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Reorder;

use Magento\ConfigurableProductSales\Model\Order\Reorder\OrderedProductAvailabilityChecker as ConfigurableProductChecker;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Reorder\OrderedProductAvailabilityChecker;
use Magento\Sales\Model\Order\Reorder\OrderedProductAvailabilityCheckerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class OrderProductAvailabilityCheckerTest
 */
class OrderProductAvailabilityCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderedProductAvailabilityCheckerInterface[]
     */
    private $productAvailabilityChecks;

    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemMock;

    /**
     * @var OrderItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemInterfaceMock;

    /**
     * @var ConfigurableProductChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableCheckerMock;

    /**
     * @var string
     */
    private $productTypeConfigurable;

    /**
     * @var string
     */
    private $productTypeSimple;

    /**
     * @var OrderedProductAvailabilityChecker
     */
    private $checker;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderItemMock = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $this->orderItemInterfaceMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productTypeConfigurable = 'configurable';
        $this->productTypeSimple = 'simple';
        $this->configurableCheckerMock = $this->getMockBuilder(ConfigurableProductChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fakeClass = new \stdClass();
        $this->productAvailabilityChecks[$this->productTypeConfigurable] = $this->configurableCheckerMock;
        $this->productAvailabilityChecks[$this->productTypeSimple] = $fakeClass;

        $this->checker = $objectManager->getObject(
            OrderedProductAvailabilityChecker::class,
            [
                'productAvailabilityChecks' => $this->productAvailabilityChecks
            ]
        );
    }

    public function testIsAvailableTrue()
    {
        $this->getProductType($this->productTypeConfigurable);
        $this->isAvailable(true);
        $this->assertTrue($this->checker->isAvailable($this->orderItemMock));
    }

    public function testIsAvailableFalse()
    {
        $this->getProductType($this->productTypeConfigurable);
        $this->isAvailable(false);
        $this->assertFalse($this->checker->isAvailable($this->orderItemMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     */
    public function testIsAvailableException()
    {
        $this->getProductType($this->productTypeSimple);
        $this->checker->isAvailable($this->orderItemMock);
    }

    public function testIsAvailableTypeNotChecks()
    {
        $this->getProductType('test_type');
        $this->assertTrue($this->checker->isAvailable($this->orderItemMock));
    }

    /**
     * @param string $productType
     */
    private function getProductType($productType)
    {
        $this->orderItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->orderItemInterfaceMock);
        $this->orderItemInterfaceMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);
    }

    /**
     * @param bool $result
     */
    private function isAvailable($result)
    {
        $this->configurableCheckerMock->expects($this->once())
            ->method('isAvailable')
            ->with($this->orderItemMock)
            ->willReturn($result);
    }
}
