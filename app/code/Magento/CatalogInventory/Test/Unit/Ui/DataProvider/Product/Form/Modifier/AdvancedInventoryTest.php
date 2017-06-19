<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;

/**
 * Class AdvancedInventoryTest
 */
class AdvancedInventoryTest extends AbstractModifierTest
{
    /**
     * @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistryMock;

    /**
     * @var StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemMock;

    /**
     * @var StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonValidatorMock;

    protected function setUp()
    {
        parent::setUp();
        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->serializerMock = $this->getMock(Json::class);
        $this->jsonValidatorMock = $this->getMockBuilder(\Magento\Framework\Serialize\JsonValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            AdvancedInventory::class,
            [
                'locator' => $this->locatorMock,
                'stockRegistry' => $this->stockRegistryMock,
                'stockConfiguration' => $this->stockConfigurationMock,
                'arrayManager' => $this->arrayManagerMock,
                'serializer' => $this->serializerMock,
                'jsonValidator' => $this->jsonValidatorMock,
            ]
        );
    }

    public function testModifyMeta()
    {
        $this->assertNotEmpty($this->getModel()->modifyMeta(['meta_key' => 'meta_value']));
    }

    /**
     * @param int $modelId
     * @param int $someData
     * @param int|string $defaultConfigValue
     * @param null|array $unserializedValue
     * @param int $serializeCalledNum
     * @param int $isValidCalledNum
     * @dataProvider modifyDataProvider
     */
    public function testModifyData(
        $modelId,
        $someData,
        $defaultConfigValue,
        $unserializedValue = null,
        $serializeCalledNum = 0,
        $isValidCalledNum = 0
    ) {
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);

        $this->stockConfigurationMock->expects($this->any())
            ->method('getDefaultConfigValue')
            ->willReturn($defaultConfigValue);

        $this->serializerMock->expects($this->exactly($serializeCalledNum))
            ->method('unserialize')
            ->with($defaultConfigValue)
            ->willReturn($unserializedValue);

        $this->jsonValidatorMock->expects($this->exactly($isValidCalledNum))
            ->method('isValid')
            ->willReturn(true);

        $this->stockItemMock->expects($this->once())->method('getData')->willReturn(['someData']);
        $this->stockItemMock->expects($this->once())->method('getManageStock')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMinSaleQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMaxSaleQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsQtyDecimal')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsDecimalDivided')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getBackorders')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getNotifyStockQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getEnableQtyIncrements')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getQtyIncrements')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsInStock')->willReturn($someData);

        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with('1/product/stock_data/min_qty_allowed_in_shopping_cart')
            ->willReturnArgument(1);

        $this->assertArrayHasKey($modelId, $this->getModel()->modifyData([]));
    }

    /**
     * @return array
     */
    public function modifyDataProvider()
    {
        return [
            [1, 1, 1],
            [1, 1, '{"36000":2}', ['36000' => 2], 1, 1]
        ];
    }
}
