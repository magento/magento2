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
 * @group module:Mage_Catalog
 */
class Mage_Catalog_Model_Product_TypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param sring|null $typeId
     * @param string $expectedClass
     * @dataProvider factoryDataProvider
     */
    public function testFactory($typeId, $expectedClass)
    {
        $product = new Varien_Object;
        if ($typeId) {
            $product->setTypeId($typeId);
        }
        $type = Mage_Catalog_Model_Product_Type::factory($product);
        $this->assertInstanceOf($expectedClass, $type);
    }

    /**
     * @return array
     */
    public function factoryDataProvider()
    {
        return array(
            array(null, 'Mage_Catalog_Model_Product_Type_Simple'),
            array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, 'Mage_Catalog_Model_Product_Type_Simple'),
            array(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL, 'Mage_Catalog_Model_Product_Type_Virtual'),
            array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, 'Mage_Catalog_Model_Product_Type_Grouped'),
            array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, 'Mage_Catalog_Model_Product_Type_Configurable'),
            array(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, 'Mage_Bundle_Model_Product_Type'),
            array(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE, 'Mage_Downloadable_Model_Product_Type'),
        );
    }

    /**
     * @param sring|null $typeId
     * @dataProvider factoryReturnsSingletonDataProvider
     */
    public function testFactoryReturnsSingleton($typeId)
    {
        $product = new Varien_Object;
        if ($typeId) {
            $product->setTypeId($typeId);
        }

        $type = Mage_Catalog_Model_Product_Type::factory($product);
        $otherType = Mage_Catalog_Model_Product_Type::factory($product);
        $this->assertSame($otherType, $type);
    }

    /**
     * @return array
     */
    public function factoryReturnsSingletonDataProvider()
    {
        return array(
            array(null),
            array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE),
            array(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL),
            array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED),
            array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE),
            array(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE),
            array(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE)
        );
    }

    /**
     * @param sring|null $typeId
     * @param string $expectedClass
     * @dataProvider priceFactoryDataProvider
     */
    public function testPriceFactory($typeId, $expectedClass)
    {
        $type = Mage_Catalog_Model_Product_Type::priceFactory($typeId);
        $this->assertInstanceOf($expectedClass, $type);
    }

    public function priceFactoryDataProvider()
    {
        return array(
            array(null, 'Mage_Catalog_Model_Product_Type_Price'),
            array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, 'Mage_Catalog_Model_Product_Type_Price'),
            array(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL, 'Mage_Catalog_Model_Product_Type_Price'),
            array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, 'Mage_Catalog_Model_Product_Type_Price'),
            array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                'Mage_Catalog_Model_Product_Type_Configurable_Price'
            ),
            array(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, 'Mage_Bundle_Model_Product_Price'),
            array(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE, 'Mage_Downloadable_Model_Product_Price'),
        );
    }

    public function testGetOptionArray()
    {
        $options = Mage_Catalog_Model_Product_Type::getOptionArray();
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $options);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL, $options);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $options);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $options);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $options);
        $this->assertArrayHasKey(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE, $options);
    }

    public function testGetAllOption()
    {
        $options = Mage_Catalog_Model_Product_Type::getAllOption();
        $this->assertTrue(isset($options[0]['value']));
        $this->assertTrue(isset($options[0]['label']));
        // doesn't make sense to test other values, because the structure of resulting array is inconsistent
    }

    public function testGetAllOptions()
    {
        $options = Mage_Catalog_Model_Product_Type::getAllOptions();
        $types = $this->_assertOptions($options);
        $this->assertContains('', $types);
    }

    public function testGetOptions()
    {
        $options = Mage_Catalog_Model_Product_Type::getOptions();
        $this->_assertOptions($options);
    }

    /**
     * @param string $typeId
     * @dataProvider getOptionTextDataProvider
     */
    public function testGetOptionText($typeId)
    {
        $this->assertNotEmpty(Mage_Catalog_Model_Product_Type::getOptionText($typeId));
    }

    public function getOptionTextDataProvider()
    {
        return array(
            array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE),
            array(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL),
            array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED),
            array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE),
            array(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE),
            array(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE),
        );
    }

    public function testGetTypes()
    {
        $types = Mage_Catalog_Model_Product_Type::getTypes();
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $types);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL, $types);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $types);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $types);
        $this->assertArrayHasKey(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $types);
        $this->assertArrayHasKey(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE, $types);
        foreach ($types as $type) {
            $this->assertArrayHasKey('label', $type);
            $this->assertArrayHasKey('model', $type);
            $this->assertArrayHasKey('composite', $type);
            // possible bug: index_priority is not defined for each type
        }
    }

    public function testGetCompositeTypes()
    {
        $types = Mage_Catalog_Model_Product_Type::getCompositeTypes();
        $this->assertInternalType('array', $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $types);
    }

    public function testGetTypesByPriority()
    {
        $types = Mage_Catalog_Model_Product_Type::getTypesByPriority();

        // collect the types and priority in the same order as the method returns
        $result = array();
        foreach ($types as $typeId => $type) {
            if (!isset($type['index_priority'])) { // possible bug: index_priority is not defined for each type
                $priority = 0;
            } else {
                $priority = (int)$type['index_priority'];
            }
            // non-composite must be before composite
            if (1 != $type['composite']) {
                $priority = -1 * $priority;
            }
            $result[$typeId] = $priority;
        }

        $expectedResult = $result;
        asort($expectedResult);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Perform assertions on type "options" structure
     *
     * @param array $options
     * @return array collected types found in options
     */
    protected function _assertOptions($options)
    {
        $this->assertInternalType('array', $options);
        $types = array();
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $types[] = $option['value'];
        }
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $types);
        $this->assertContains(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $types);
        $this->assertContains(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE, $types);
        return $types;
    }
}
