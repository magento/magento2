<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_helper->expects($this->any())->method('isPriceGlobal')->willReturn(true);

        $currencyFactoryMock = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $productTypeMock = $this->createMock(Type::class);
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $localeFormatMock = $this->getMockForAbstractClass(FormatInterface::class);
        $groupManagement = $this->getMockForAbstractClass(GroupManagementInterface::class);
        $scopeOverriddenValue = $this->createMock(ScopeOverriddenValue::class);
        $this->_model = $this->getMockForAbstractClass(
            AbstractGroupPrice::class,
            [
                'currencyFactory' => $currencyFactoryMock,
                'storeManager' => $storeManagerMock,
                'catalogData' => $this->_helper,
                'config' => $configMock,
                'localeFormat' => $localeFormatMock,
                'catalogProductType' => $productTypeMock,
                'groupManagement' => $groupManagement,
                'scopeOverriddenValue' => $scopeOverriddenValue
            ]
        );
        $resource = $this->getMockBuilder(\stdClass::class)->addMethods(['getMainTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())->method('getMainTable')->willReturn('table');

        $this->_model->expects($this->any())->method('_getResource')->willReturn($resource);
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName']
        );
        $attribute->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $attribute->expects($this->any())->method('isStatic')->willReturn(false);
        $attribute->expects($this->any())->method('getBackendTable')->willReturn('table');
        $attribute->expects($this->any())->method('getName')->willReturn('tier_price');
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
