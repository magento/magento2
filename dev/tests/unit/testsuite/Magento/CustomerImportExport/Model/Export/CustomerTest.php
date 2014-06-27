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
namespace Magento\CustomerImportExport\Model\Export;

class CustomerTest extends \PHPUnit_Framework_TestCase
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
    protected $_websites = array(\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1');

    /**
     * Stores array (store id => code)
     *
     * @var array
     */
    protected $_stores = array(0 => 'admin', 1 => 'store1');

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = array(array('attribute_id' => 1, 'attribute_code' => self::ATTRIBUTE_CODE));

    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData = array('website_id' => 1, 'store_id' => 1, self::ATTRIBUTE_CODE => 1);

    /**
     * Customer export model
     *
     * @var Customer
     */
    protected $_model;

    protected function setUp()
    {
        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);

        $storeManager->expects(
            $this->any()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback(array($this, 'getWebsites'))
        );

        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->will(
            $this->returnCallback(array($this, 'getStores'))
        );

        $this->_model = new Customer(
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $storeManager,
            $this->getMock('Magento\ImportExport\Model\Export\Factory', array(), array(), '', false),
            $this->getMock(
                'Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory',
                array(),
                array(),
                '',
                false
            ),
            $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface', array(), array(), '', false),
            $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\Resource\Customer\CollectionFactory', array(), array(), '', false),
            $this->_getModelDependencies()
        );
    }

    protected function tearDown()
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
        $translator = $this->getMock('stdClass');

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeCollection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false)
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $objectManagerHelper->getConstructArguments(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                array('eavTypeFactory' => $this->getMock('Magento\Eav\Model\Entity\TypeFactory'))
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $arguments,
                '',
                true,
                true,
                true,
                array('_construct')
            );
            $attributeCollection->addItem($attribute);
        }

        $data = array(
            'translator' => $translator,
            'attribute_collection' => $attributeCollection,
            'page_size' => 1,
            'collection_by_pages_iterator' => 'not_used',
            'entity_type_id' => 1,
            'customer_collection' => 'not_used'
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
            if (!$withDefault && $id == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = array('id' => $id, 'code' => $code);
            $websites[$id] = new \Magento\Framework\Object($websiteData);
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
            $storeData = array('id' => $id, 'code' => $code);
            $stores[$id] = new \Magento\Framework\Object($storeData);
        }

        return $stores;
    }

    /**
     * Test for method exportItem()
     *
     * @covers \Magento\CustomerImportExport\Model\Export\Customer::exportItem
     */
    public function testExportItem()
    {
        /** @var $writer \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter */
        $writer = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\Adapter\AbstractAdapter',
            array(),
            '',
            false,
            false,
            true,
            array('writeRow')
        );

        $writer->expects(
            $this->once()
        )->method(
            'writeRow'
        )->will(
            $this->returnCallback(array($this, 'validateWriteRow'))
        );

        $this->_model->setWriter($writer);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments('Magento\Framework\Model\AbstractModel');
        $arguments['data'] = $this->_customerData;
        $item = $this->getMockForAbstractClass('Magento\Framework\Model\AbstractModel', $arguments);

        $this->_model->exportItem($item);
    }

    /**
     * Validate data passed to writer's writeRow() method
     *
     * @param array $row
     */
    public function validateWriteRow(array $row)
    {
        $websiteColumn = Customer::COLUMN_WEBSITE;
        $storeColumn = Customer::COLUMN_STORE;
        $this->assertEquals($this->_websites[$this->_customerData['website_id']], $row[$websiteColumn]);
        $this->assertEquals($this->_stores[$this->_customerData['store_id']], $row[$storeColumn]);
        $this->assertEquals($this->_customerData[self::ATTRIBUTE_CODE], $row[self::ATTRIBUTE_CODE]);
    }
}
