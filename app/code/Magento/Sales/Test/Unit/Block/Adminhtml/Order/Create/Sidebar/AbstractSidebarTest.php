<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractSidebarTest extends TestCase
{
    /**
     * @var AbstractSidebar
     */
    protected $abstractSidebar;

    /**
     * @var MockObject
     */
    protected $itemMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->itemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractSidebar = $helper->getObject(
            AbstractSidebar::class,
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
        $this->itemMock->expects($this->exactly($itemQty))->method('getQty')->willReturn($qty);
        $this->assertEquals($expectedValue, $this->abstractSidebar->getItemQty($this->itemMock));
    }

    /**
     * @return array
     */
    public function getItemQtyDataProvider()
    {
        return ['whenQtyIsset' => [2, 10, 10], 'whenQtyNotIsset' => [1, false, 1]];
    }

    public function testIsConfigurationRequired()
    {
        $productTypeMock = $this->createMock(Type::class);
        $this->assertFalse($this->abstractSidebar->isConfigurationRequired($productTypeMock));
    }
}
