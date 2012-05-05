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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Layer_Filter_Attribute.
 *
 * @magentoDataFixture Mage/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
 */
class Mage_Catalog_Model_Layer_Filter_AttributeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Layer_Filter_Attribute
     */
    protected $_model;

    /**
     * @var int
     */
    protected $_attributeOptionId;

    protected function setUp()
    {
        $attribute = new Mage_Catalog_Model_Entity_Attribute();
        $attribute->loadByCode('catalog_product', 'attribute_with_option');
        foreach ($attribute->getSource()->getAllOptions() as $optionInfo) {
            if ($optionInfo['label'] == 'Option Label') {
                $this->_attributeOptionId = $optionInfo['value'];
                break;
            }
        }
        $this->assertNotEmpty($this->_attributeOptionId, 'Fixture attribute option id.'); // just in case

        $this->_model = new Mage_Catalog_Model_Layer_Filter_Attribute;
        $this->_model->setData(array(
            'layer' => new Mage_Catalog_Model_Layer(),
            'attribute_model' => $attribute,
        ));
    }

    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());

        $request = new Magento_Test_Request();
        $request->setParam('attribute', array());
        $this->_model->apply($request, new Mage_Core_Block_Text());

        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());
    }

    public function testApply()
    {
        $this->assertEmpty($this->_model->getLayer()->getState()->getFilters());

        $request = new Magento_Test_Request();
        $request->setParam('attribute', $this->_attributeOptionId);
        $this->_model->apply($request, new Mage_Core_Block_Text());

        $this->assertNotEmpty($this->_model->getLayer()->getState()->getFilters());
    }

    public function testGetItems()
    {
        $items = $this->_model->getItems();

        $this->assertInternalType('array', $items);
        $this->assertEquals(1, count($items));

        /** @var $item Mage_Catalog_Model_Layer_Filter_Item */
        $item = $items[0];

        $this->assertInstanceOf('Mage_Catalog_Model_Layer_Filter_Item', $item);
        $this->assertSame($this->_model, $item->getFilter());
        $this->assertEquals('Option Label', $item->getLabel());
        $this->assertEquals($this->_attributeOptionId, $item->getValue());
        $this->assertEquals(1, $item->getCount());
    }
}
