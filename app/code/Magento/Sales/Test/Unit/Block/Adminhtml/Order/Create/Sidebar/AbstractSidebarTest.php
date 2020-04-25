<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->itemMock = $this->createPartialMock(DataObject::class, ['getQty']);
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
        $this->itemMock->expects($this->exactly($itemQty))->method('getQty')->will($this->returnValue($qty));
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
        $this->assertEquals(false, $this->abstractSidebar->isConfigurationRequired($productTypeMock));
    }
}
