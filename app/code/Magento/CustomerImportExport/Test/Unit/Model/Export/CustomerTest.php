<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\Export;

use Magento\CustomerImportExport\Model\Export\Customer;

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
    protected $_websites = [\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1'];

    /**
     * Stores array (store id => code)
     *
     * @var array
     */
    protected $_stores = [0 => 'admin', 1 => 'store1'];

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = [['attribute_id' => 1, 'attribute_code' => self::ATTRIBUTE_CODE]];

    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData = ['website_id' => 1, 'store_id' => 1, self::ATTRIBUTE_CODE => 1];

    /**
     * Customer export model
     *
     * @var Customer
     */
    protected $_model;

    protected function setUp()
    {
        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);

        $storeManager->expects(
            $this->any()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback([$this, 'getWebsites'])
        );

        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->will(
            $this->returnCallback([$this, 'getStores'])
        );

        $this->_model = new \Magento\CustomerImportExport\Model\Export\Customer(
            $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
            $storeManager,
            $this->getMock('Magento\ImportExport\Model\Export\Factory', [], [], '', false),
            $this->getMock(
                'Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory',
                [],
                [],
                '',
                false
            ),
            $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface', [], [], '', false),
            $this->getMock('Magento\Eav\Model\Config', [], [], '', false),
            $this->getMock('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory', [], [], '', false),
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

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $attributeCollection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $objectManagerHelper->getConstructArguments(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                ['eavTypeFactory' => $this->getMock('Magento\Eav\Model\Entity\TypeFactory', [], [], '', false)]
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                $arguments,
                '',
                true,
                true,
                true,
                ['_construct']
            );
            $attributeCollection->addItem($attribute);
        }

        $data = [
            'translator' => $translator,
            'attribute_collection' => $attributeCollection,
            'page_size' => 1,
            'collection_by_pages_iterator' => 'not_used',
            'entity_type_id' => 1,
            'customer_collection' => 'not_used',
        ];

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
        $websites = [];
        if (!$withDefault) {
            unset($websites[0]);
        }
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = ['id' => $id, 'code' => $code];
            $websites[$id] = new \Magento\Framework\DataObject($websiteData);
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
        $stores = [];
        if (!$withDefault) {
            unset($stores[0]);
        }
        foreach ($this->_stores as $id => $code) {
            if (!$withDefault && $id == 0) {
                continue;
            }
            $storeData = ['id' => $id, 'code' => $code];
            $stores[$id] = new \Magento\Framework\DataObject($storeData);
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
            [],
            '',
            false,
            false,
            true,
            ['writeRow']
        );

        $writer->expects(
            $this->once()
        )->method(
            'writeRow'
        )->will(
            $this->returnCallback([$this, 'validateWriteRow'])
        );

        $this->_model->setWriter($writer);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
