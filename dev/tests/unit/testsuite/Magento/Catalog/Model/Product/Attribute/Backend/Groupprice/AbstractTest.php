<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend\Groupprice;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice\AbstractGroupprice
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
        $this->_helper = $this->getMock('Magento\Catalog\Helper\Data', array('isPriceGlobal'), array(), '', false);
        $this->_helper->expects($this->any())->method('isPriceGlobal')->will($this->returnValue(true));

        $loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $currencyFactoryMock = $this->getMock('Magento\Directory\Model\CurrencyFactory', array(), array(), '', false);
        $storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface', array(), array(), '', false);
        $productTypeMock = $this->getMock('Magento\Catalog\Model\Product\Type', array(), array(), '', false);
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Product\Attribute\Backend\Groupprice\AbstractGroupprice',
            array(
                'logger' => $loggerMock,
                'currencyFactory' => $currencyFactoryMock,
                'storeManager' => $storeManagerMock,
                'catalogData' => $this->_helper,
                'config' => $configMock,
                'catalogProductType' => $productTypeMock
            )
        );
        $resource = $this->getMock('StdClass', array('getMainTable'));
        $resource->expects($this->any())->method('getMainTable')->will($this->returnValue('table'));

        $this->_model->expects($this->any())->method('_getResource')->will($this->returnValue($resource));
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array('getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup'),
            array(),
            '',
            false
        );
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));

        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));

        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $attribute->expects($this->any())->method('getName')->will($this->returnValue('group_price'));

        $this->_model->setAttribute($attribute);

        $object = new \Magento\Framework\Object();
        $object->setGroupPrice(array(array('price_id' => 10)));
        $object->setId(555);

        $this->assertEquals(
            array(
                'table' => array(
                    array('value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId())
                )
            ),
            $this->_model->getAffectedFields($object)
        );
    }
}
