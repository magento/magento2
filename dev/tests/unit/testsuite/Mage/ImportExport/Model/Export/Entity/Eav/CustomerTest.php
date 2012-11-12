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
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ImportExport_Model_Export_Entity_Eav_CustomerTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**#@+
     * Test attribute code
     */
    const ATTRIBUTE_CODE = 'code1';
    /**#@-*/

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = array(
        Mage_Core_Model_App::ADMIN_STORE_ID => 'admin',
        1                                   => 'website1',
    );

    /**
     * Stores array (store id => code)
     *
     * @var array
     */
    protected $_stores = array(
        0 => 'admin',
        1 => 'store1',
    );

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = array(
        array(
            'attribute_id'   => 1,
            'attribute_code' => self::ATTRIBUTE_CODE,
        )
    );

    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData = array(
        'website_id'         => 1,
        'store_id'           => 1,
        self::ATTRIBUTE_CODE => 1,
    );

    /**
     * Customer export model
     *
     * @var Mage_ImportExport_Model_Export_Entity_Eav_Customer
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_ImportExport_Model_Export_Entity_Eav_Customer($this->_getModelDependencies());
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $websiteManager = $this->getMock('stdClass', array('getWebsites'));
        $websiteManager->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnCallback(array($this, 'getWebsites')));

        $storeManager = $this->getMock('stdClass', array('getStores'));
        $storeManager->expects($this->once())
            ->method('getStores')
            ->will($this->returnCallback(array($this, 'getStores')));

        $translator = $this->getMock('stdClass', array('__'));
        $translator->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));

        $attributeCollection = new Varien_Data_Collection();
        foreach ($this->_attributes as $attributeData) {
            $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass('Mage_Eav_Model_Entity_Attribute_Abstract',
                $arguments, '', true, true, true, array('_construct')
            );
            $attributeCollection->addItem($attribute);
        }

        $data = array(
            'website_manager'              => $websiteManager,
            'store_manager'                => $storeManager,
            'translator'                   => $translator,
            'attribute_collection'         => $attributeCollection,
            'page_size'                    => 1,
            'collection_by_pages_iterator' => 'not_used',
            'entity_type_id'               => 1,
            'customer_collection'          => 'not_used'
        );

        return $data;
    }

    /**
     * Get websites
     *
     * @param bool $withDefault
     * @return array
     */
    public function getWebsites($withDefault = false)
    {
        $websites = array();
        if (!$withDefault) {
            unset($websites[0]);
        }
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == Mage_Core_Model_App::ADMIN_STORE_ID) {
                continue;
            }
            $websiteData = array(
                'id'   => $id,
                'code' => $code,
            );
            $websites[$id] = new Varien_Object($websiteData);
        }

        return $websites;
    }

    /**
     * Get stores
     *
     * @param bool $withDefault
     * @return array
     */
    public function getStores($withDefault = false)
    {
        $stores = array();
        if (!$withDefault) {
            unset($stores[0]);
        }
        foreach ($this->_stores as $id => $code) {
            if (!$withDefault && $id == 0) {
                continue;
            }
            $storeData = array(
                'id'   => $id,
                'code' => $code,
            );
            $stores[$id] = new Varien_Object($storeData);
        }

        return $stores;
    }

    /**
     * Test for method exportItem()
     *
     * @covers Mage_ImportExport_Model_Export_Entity_Eav_Customer::exportItem
     */
    public function testExportItem()
    {
        /** @var $writer Mage_ImportExport_Model_Export_Adapter_Abstract */
        $writer = $this->getMockForAbstractClass('Mage_ImportExport_Model_Export_Adapter_Abstract',
            array(), '', false, false, true, array('writeRow')
        );

        $writer->expects($this->once())
            ->method('writeRow')
            ->will($this->returnCallback(array($this, 'validateWriteRow')));

        $this->_model->setWriter($writer);

        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $arguments['data'] = $this->_customerData;
        $item = $this->getMockForAbstractClass('Mage_Core_Model_Abstract', $arguments);

        $this->_model->exportItem($item);
    }

    /**
     * Validate data passed to writer's writeRow() method
     *
     * @param array $row
     */
    public function validateWriteRow(array $row)
    {
        $websiteColumn = Mage_ImportExport_Model_Export_Entity_Eav_Customer::COLUMN_WEBSITE;
        $storeColumn = Mage_ImportExport_Model_Export_Entity_Eav_Customer::COLUMN_STORE;
        $this->assertEquals($this->_websites[$this->_customerData['website_id']], $row[$websiteColumn]);
        $this->assertEquals($this->_stores[$this->_customerData['store_id']], $row[$storeColumn]);
        $this->assertEquals($this->_customerData[self::ATTRIBUTE_CODE], $row[self::ATTRIBUTE_CODE]);
    }
}
