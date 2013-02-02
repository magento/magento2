<?php
/**
 * Product custom options API model test.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/CustomOption.php
 * @magentoDbIsolation enabled
 */
class Mage_Catalog_Model_Product_Option_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $_createdOptionAfter;

    /** @var SimpleXmlElement */
    protected static $_customOptionFixture;

    public static function setUpBeforeClass()
    {
        self::$_customOptionFixture = self::_loadXmlFixture('CustomOption.xml');
    }

    /**
     * Product Custom Option CRUD test
     */
    public function testCustomOptionCRUD()
    {
        $customOptions = Magento_Test_Helper_Api::simpleXmlToArray(self::$_customOptionFixture->customOptionsToAdd);
        $store = (string)self::$_customOptionFixture->store;

        $this->_testCreate($store, $customOptions);
        $this->_testRead($store, $customOptions);
        $optionsToUpdate = Magento_Test_Helper_Api::simpleXmlToArray(
            self::$_customOptionFixture->customOptionsToUpdate
        );
        $this->_testUpdate($optionsToUpdate);
    }

    /**
     * Test creating custom options
     *
     * @param string $store
     * @param array $customOptions
     */
    protected function _testCreate($store, $customOptions)
    {
        $fixtureProductId = Mage::registry('productData')->getId();
        $createdOptionBefore = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionList',
            array(
                'productId' => $fixtureProductId,
                'store' => $store
            )
        );
        $this->assertEmpty($createdOptionBefore);

        foreach ($customOptions as $option) {
            if (isset($option['additional_fields'])
                and !is_array(reset($option['additional_fields']))
            ) {
                $option['additional_fields'] = array($option['additional_fields']);
            }

            $addedOptionResult = Magento_Test_Helper_Api::call(
                $this,
                'catalogProductCustomOptionAdd',
                array(
                    'productId' => $fixtureProductId,
                    'data' => (object)$option,
                    'store' => $store
                )
            );
            $this->assertTrue((bool)$addedOptionResult);
        }
    }

    /**
     * Test reading custom options
     *
     * @param string $store
     * @param array $customOptions
     */
    protected function _testRead($store, $customOptions)
    {
        $fixtureProductId = Mage::registry('productData')->getId();
        self::$_createdOptionAfter = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionList',
            array(
                'productId' => $fixtureProductId,
                'store' => $store
            )
        );

        $this->assertTrue(is_array(self::$_createdOptionAfter));
        $this->assertEquals(count($customOptions), count(self::$_createdOptionAfter));

        foreach (self::$_createdOptionAfter as $option) {
            $this->assertEquals($customOptions[$option->type]['title'], $option->title);
        }
    }

    /**
     * Test updating custom option
     *
     * @param array $optionsToUpdate
     */
    protected function _testUpdate($optionsToUpdate)
    {
        $updateCounter = 0;
        foreach (self::$_createdOptionAfter as $option) {
            $option = (array)$option;
            $optionInfo = Magento_Test_Helper_Api::call(
                $this,
                'catalogProductCustomOptionInfo',
                array(
                    'optionId' => $option['option_id']
                )
            );

            $this->assertTrue(is_array($optionInfo));
            $this->assertGreaterThan(3, count($optionInfo));

            if (isset($optionsToUpdate[$option['type']])) {
                $toUpdateValues = $optionsToUpdate[$option['type']];
                if (isset($toUpdateValues['additional_fields'])
                    and !is_array(reset($toUpdateValues['additional_fields']))
                ) {
                    $toUpdateValues['additional_fields'] = array($toUpdateValues['additional_fields']);
                }

                $updateOptionResult = Magento_Test_Helper_Api::call(
                    $this,
                    'catalogProductCustomOptionUpdate',
                    array(
                        'optionId' => $option['option_id'],
                        'data' => $toUpdateValues
                    )
                );
                $this->assertTrue((bool)$updateOptionResult);
                $updateCounter++;

                $this->_testOptionsAfterUpdate($option['option_id'], $toUpdateValues);
            }
        }

        $this->assertCount($updateCounter, $optionsToUpdate);
    }

    /**
     * Check that options has been updated correctly
     *
     * @param int $optionId
     * @param array $toUpdateValues
     */
    protected function _testOptionsAfterUpdate($optionId, $toUpdateValues)
    {
        $optionAfterUpdate = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionInfo',
            array(
                'optionId' => $optionId
            )
        );

        foreach ($toUpdateValues as $key => $value) {
            if (is_string($value)) {
                self::assertEquals($value, $optionAfterUpdate[$key]);
            }
        }

        if (isset($toUpdateValues['additional_fields'])) {
            $updateFields = reset($toUpdateValues['additional_fields']);
            $actualFields = reset($optionAfterUpdate['additional_fields']);
            foreach ($updateFields as $key => $value) {
                if (is_string($value)) {
                    self::assertEquals($value, $actualFields[$key]);
                }
            }
        }
    }

    /**
     * Product Custom Option ::types() method test
     */
    public function testCustomOptionTypes()
    {
        $attributeSetFixture = $this->_loadXmlFixture('CustomOptionTypes.xml');
        $customOptionsTypes = Magento_Test_Helper_Api::simpleXmlToArray($attributeSetFixture);

        $optionTypes = Magento_Test_Helper_Api::call($this, 'catalogProductCustomOptionTypes', array());
        $this->assertEquals($customOptionsTypes['customOptionTypes']['types'], $optionTypes);
    }

    /**
     * Update custom option
     *
     * @param int $optionId
     * @param array $option
     * @param int $store
     *
     * @return boolean
     */
    protected function _updateOption($optionId, $option, $store = null)
    {
        if (isset($option['additional_fields'])
            and !is_array(reset($option['additional_fields']))
        ) {
            $option['additional_fields'] = array($option['additional_fields']);
        }

        return Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionUpdate',
            array(
                'optionId' => $optionId,
                'data' => $option,
                'store' => $store
            )
        );
    }

    /**
     * Test option add exception: product_not_exists
     */
    public function testCustomOptionAddExceptionProductNotExists()
    {
        $customOptions = Magento_Test_Helper_Api::simpleXmlToArray(self::$_customOptionFixture->customOptionsToAdd);

        $option = reset($customOptions);
        if (isset($option['additional_fields'])
            and !is_array(reset($option['additional_fields']))
        ) {
            $option['additional_fields'] = array($option['additional_fields']);
        }
        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionAdd',
            array(
                'productId' => 'invalid_id',
                'data' => $option
            )
        );
    }

    /**
     * Test option add without additional fields exception: invalid_data
     */
    public function testCustomOptionAddExceptionAdditionalFieldsNotSet()
    {
        $fixtureProductId = Mage::registry('productData')->getId();
        $customOptions = Magento_Test_Helper_Api::simpleXmlToArray(self::$_customOptionFixture->customOptionsToAdd);

        $option = $customOptions['field'];
        $option['additional_fields'] = array();

        $this->setExpectedException('SoapFault', 'Provided data is invalid.');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionAdd',
            array('productId' => $fixtureProductId, 'data' => $option)
        );
    }

    /**
     * Test option date_time add with store id exception: store_not_exists
     */
    public function testCustomOptionDateTimeAddExceptionStoreNotExist()
    {
        $fixtureProductId = Mage::registry('productData')->getId();
        $customOptions = Magento_Test_Helper_Api::simpleXmlToArray(self::$_customOptionFixture->customOptionsToAdd);

        $option = reset($customOptions);
        if (isset($option['additional_fields'])
            and !is_array(reset($option['additional_fields']))
        ) {
            $option['additional_fields'] = array($option['additional_fields']);
        }
        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionAdd',
            array(
                'productId' => $fixtureProductId,
                'data' => $option,
                'store' => 'some_store_name'
            )
        );
    }

    /**
     * Test product custom options list exception: product_not_exists
     */
    public function testCustomOptionListExceptionProductNotExists()
    {
        $store = (string)self::$_customOptionFixture->store;

        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionList',
            array(
                'productId' => 'unknown_id',
                'store' => $store
            )
        );
    }

    /**
     * Test product custom options list exception: store_not_exists
     */
    public function testCustomOptionListExceptionStoreNotExists()
    {
        $fixtureProductId = Mage::registry('productData')->getId();

        $this->setExpectedException('SoapFault');
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionList',
            array(
                'productId' => $fixtureProductId,
                'store' => 'unknown_store_name'
            )
        );
    }

    /**
     * Test option add with invalid type
     *
     * @expectedException SoapFault
     */
    public function testCustomOptionUpdateExceptionInvalidType()
    {
        $optionsToUpdate = Magento_Test_Helper_Api::simpleXmlToArray(
            self::$_customOptionFixture->customOptionsToUpdate
        );
        $option = (array)reset(self::$_createdOptionAfter);

        $toUpdateValues = $optionsToUpdate[$option['type']];
        $toUpdateValues['type'] = 'unknown_type';

        $this->_updateOption($option['option_id'], $toUpdateValues);
    }

    /**
     * Test option remove and exception
     *
     * @expectedException SoapFault
     * @depends testCustomOptionUpdateExceptionInvalidType
     */
    public function testCustomOptionRemove()
    {
        // Remove
        foreach (self::$_createdOptionAfter as $option) {
            $removeOptionResult = Magento_Test_Helper_Api::call(
                $this,
                'catalogProductCustomOptionRemove',
                // @codingStandardsIgnoreStart
                array(
                    'optionId' => $option->option_id
                )
                // @codingStandardsIgnoreEnd
            );
            $this->assertTrue((bool)$removeOptionResult);
        }

        // Delete exception test
        Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCustomOptionRemove',
            array('optionId' => mt_rand(99999, 999999))
        );
    }

    /**
     * Load XML from fixture
     *
     * @param string $fileName
     * @return SimpleXMLElement
     */
    protected static function _loadXmlFixture($fileName)
    {
        return simplexml_load_file(dirname(__FILE__) . '/../Api/_files/_data/xml/' . $fileName);
    }
}
