<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Sidebar;

class AbstractSidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
     */
    protected $abstractSidebar;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemMock = $this->getMock(\Magento\Framework\DataObject::class, ['getQty'], [], '', false);
        $this->abstractSidebar = $helper->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar::class,
            []
        );
    }

    /**
     * @param int $itemQty
     * @param int|bool $qty
     * @param int $expectedValue
     * @dataProvider getItemQtyDataProvider
     */
    public function testGetItemQty($itemQty, $qty, $expectedValue)
    {
        $this->itemMock->expects($this->exactly($itemQty))->method('getQty')->will($this->returnValue($qty));
        $this->assertEquals($expectedValue, $this->abstractSidebar->getItemQty($this->itemMock));
    }

    public function getItemQtyDataProvider()
    {
        return ['whenQtyIsset' => [2, 10, 10], 'whenQtyNotIsset' => [1, false, 1]];
    }

    public function testIsConfigurationRequired()
    {
        $productTypeMock = $this->getMock(\Magento\Catalog\Model\Product\Type::class, [], [], '', false);
        $this->assertEquals(false, $this->abstractSidebar->isConfigurationRequired($productTypeMock));
    }
}
