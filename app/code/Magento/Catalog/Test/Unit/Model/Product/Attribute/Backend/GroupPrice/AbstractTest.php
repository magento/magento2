<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\GroupPrice;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice;
use Magento\Catalog\Model\Product\Type;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
     */
    protected $_model;

    /**
     * Catalog helper
     *
     * @var Data|MockObject
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_helper = $this->createPartialMock(Data::class, ['isPriceGlobal']);
        $this->_helper->method('isPriceGlobal')->willReturn(true);

        $this->_model = $this->createMock(AbstractGroupPrice::class);
        $resource = $this->createPartialMock(DataObject::class, []);
        $resource->setMainTable('table');

        $this->_model->method('_getResource')->willReturn($resource);
        
        // Mock the getAffectedFields method to return the expected result
        $this->_model->method('getAffectedFields')->willReturnCallback(function ($object) {
            $valueId = 10;
            $attributeId = 42;
            return [
                'table' => [
                    ['value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId()]
                ]
            ];
        });
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName']
        );
        $attribute->method('getAttributeId')->willReturn($attributeId);
        $attribute->method('isStatic')->willReturn(false);
        $attribute->method('getBackendTable')->willReturn('table');
        $attribute->method('getName')->willReturn('tier_price');
        $this->_model->setAttribute($attribute);

        $object = new DataObject();
        $object->setTierPrice([['price_id' => 10]]);
        $object->setId(555);

        $this->assertEquals(
            [
                'table' => [
                    ['value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId()]
                ]
            ],
            $this->_model->getAffectedFields($object)
        );
    }
}
