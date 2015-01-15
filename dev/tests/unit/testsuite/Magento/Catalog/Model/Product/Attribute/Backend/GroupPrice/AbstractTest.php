<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
     */
    protected $_model;

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = $this->getMock('Magento\Catalog\Helper\Data', ['isPriceGlobal'], [], '', false);
        $this->_helper->expects($this->any())->method('isPriceGlobal')->will($this->returnValue(true));

        $currencyFactoryMock = $this->getMock('Magento\Directory\Model\CurrencyFactory', [], [], '', false);
        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $productTypeMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $groupManagement = $this->getMock('Magento\Customer\Api\GroupManagementInterface', [], [], '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice',
            [
                'currencyFactory' => $currencyFactoryMock,
                'storeManager' => $storeManagerMock,
                'catalogData' => $this->_helper,
                'config' => $configMock,
                'catalogProductType' => $productTypeMock,
                'groupManagement' => $groupManagement
            ]
        );
        $resource = $this->getMock('StdClass', ['getMainTable']);
        $resource->expects($this->any())->method('getMainTable')->will($this->returnValue('table'));

        $this->_model->expects($this->any())->method('_getResource')->will($this->returnValue($resource));
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));

        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));

        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $attribute->expects($this->any())->method('getName')->will($this->returnValue('group_price'));

        $this->_model->setAttribute($attribute);

        $object = new \Magento\Framework\Object();
        $object->setGroupPrice([['price_id' => 10]]);
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
