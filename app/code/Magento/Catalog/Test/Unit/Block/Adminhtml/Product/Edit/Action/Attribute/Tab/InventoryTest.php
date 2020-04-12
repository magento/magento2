<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Source\Backorders;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase
{
    /**
     * @var Backorders|MockObject
     */
    protected $backordersMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory
     */
    protected $inventory;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->createPartialMock(Context::class, ['getRequest']);
        $this->backordersMock = $this->createMock(Backorders::class);
        $this->stockConfigurationMock = $this->getMockForAbstractClass(
            StockConfigurationInterface::class,
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam'],
            '',
            false
        );

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $this->inventory = $objectManager->getObject(
            Inventory::class,
            [
                'context' => $this->contextMock,
                'backorders' => $this->backordersMock,
                'stockConfiguration' => $this->stockConfigurationMock
            ]
        );
    }

    /**
     * Run test getBackordersOption method
     *
     * @return void
     */
    public function testGetBackordersOption()
    {
        $this->backordersMock->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue('return-value'));
        $this->assertEquals('return-value', $this->inventory->getBackordersOption());
    }

    /**
     * Run test getFieldSuffix method
     *
     * @return void
     */
    public function testGetFieldSuffix()
    {
        $this->assertEquals('inventory', $this->inventory->getFieldSuffix());
    }

    /**
     * Run test getStoreId method
     *
     * @return void
     */
    public function testGetStoreId()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('store')
            ->will($this->returnValue('125'));

        $this->assertTrue(is_integer($this->inventory->getStoreId()));
    }

    /**
     * Run test getDefaultConfigValue method
     *
     * @return void
     */
    public function testGetDefaultConfigValue()
    {
        $this->stockConfigurationMock->expects($this->once())
            ->method('getDefaultConfigValue')
            ->with('field-name')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->inventory->getDefaultConfigValue('field-name'));
    }

    /**
     * Run test getTabLabel method
     *
     * @return void
     */
    public function testGetTabLabel()
    {
        $this->assertEquals('Advanced Inventory', $this->inventory->getTabLabel());
    }

    /**
     * Run test getTabTitle method
     *
     * @return void
     */
    public function testGetTabTitle()
    {
        $this->assertEquals('Advanced Inventory', $this->inventory->getTabLabel());
    }

    /**
     * Run test canShowTab method
     *
     * @return void
     */
    public function testCanShowTab()
    {
        $this->assertTrue($this->inventory->canShowTab());
    }

    /**
     * Run test isHidden method
     *
     * @return void
     */
    public function testIsHidden()
    {
        $this->assertFalse($this->inventory->isHidden());
    }

    /**
     * Run test isEnabled method
     *
     * @return void
     */
    public function testIsEnabled()
    {
        $this->assertEquals(true, $this->inventory->isAvailable('field'));
    }
}
