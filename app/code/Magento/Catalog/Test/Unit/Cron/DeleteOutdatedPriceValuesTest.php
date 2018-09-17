<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Cron\DeleteOutdatedPriceValues;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\BackendInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\Store;

/**
 * @covers \Magento\Catalog\Cron\DeleteOutdatedPriceValues
 */
class DeleteOutdatedPriceValuesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testable Object
     *
     * @var DeleteOutdatedPriceValues
     */
    private $deleteOutdatedPriceValues;

    /**
     * @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ScopeConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbAdapterMock;

    /**
     * @var BackendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeBackendMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->attributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfig::class)
            ->disableOriginalConstructor()->getMock();
        $this->dbAdapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->attributeBackendMock = $this->getMockBuilder(BackendInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->deleteOutdatedPriceValues = new DeleteOutdatedPriceValues(
            $this->resourceConnectionMock,
            $this->attributeRepositoryMock,
            $this->scopeConfigMock
        );
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecute()
    {
        $table = 'catalog_product_entity_decimal';
        $attributeId = 15;
        $conditions = ['first', 'second'];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_PRICE_SCOPE)
            ->willReturn(Store::XML_PATH_PRICE_SCOPE);
        $this->attributeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, ProductAttributeInterface::CODE_PRICE)
            ->willReturn($this->attributeMock);
        $this->attributeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $this->attributeMock
            ->expects($this->once())
            ->method('getBackend')
            ->willReturn($this->attributeBackendMock);
        $this->attributeBackendMock
            ->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock
            ->expects($this->exactly(2))
            ->method('quoteInto')
            ->willReturnMap([
                ['attribute_id = ?', $attributeId, null, null, $conditions[0]],
                ['store_id != ?', Store::DEFAULT_STORE_ID, null, null, $conditions[1]],
            ]);
        $this->dbAdapterMock
            ->expects($this->once())
            ->method('delete')
            ->with($table, $conditions);
        $this->deleteOutdatedPriceValues->execute();
    }

    /**
     * Test execute method
     * The price scope config option is not equal to global value
     *
     * @return void
     */
    public function testExecutePriceConfigIsNotSetToGlobal()
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_PRICE_SCOPE)
            ->willReturn(null);
        $this->attributeRepositoryMock
            ->expects($this->never())
            ->method('get');
        $this->dbAdapterMock
            ->expects($this->never())
            ->method('delete');

        $this->deleteOutdatedPriceValues->execute();
    }
}
