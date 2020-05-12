<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Export;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\CustomerImportExport\Model\Export\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTest extends TestCase
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
    protected $_websites = [Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1'];

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

    protected function setUp(): void
    {
        $storeManager = $this->createMock(StoreManager::class);

        $storeManager->expects(
            $this->any()
        )->method(
            'getWebsites'
        )->willReturnCallback(
            [$this, 'getWebsites']
        );

        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->willReturnCallback(
            [$this, 'getStores']
        );

        $this->_model = new Customer(
            $this->getMockForAbstractClass(ScopeConfigInterface::class),
            $storeManager,
            $this->createMock(Factory::class),
            $this->createMock(CollectionByPagesIteratorFactory::class),
            $this->getMockForAbstractClass(TimezoneInterface::class),
            $this->createMock(Config::class),
            $this->createMock(CollectionFactory::class),
            $this->_getModelDependencies()
        );
    }

    protected function tearDown(): void
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
        $translator = $this->createMock(\stdClass::class);

        $objectManagerHelper = new ObjectManager($this);
        $attributeCollection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $objectManagerHelper->getConstructArguments(
                AbstractAttribute::class,
                ['eavTypeFactory' => $this->createMock(TypeFactory::class)]
            );
            $arguments['data'] = $attributeData;
            $attribute = $this->getMockForAbstractClass(
                AbstractAttribute::class,
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
        foreach ($this->_websites as $id => $code) {
            if (!$withDefault && $id == Store::DEFAULT_STORE_ID) {
                continue;
            }
            $websiteData = ['id' => $id, 'code' => $code];
            $websites[$id] = new DataObject($websiteData);
        }
        if (!$withDefault) {
            unset($websites[0]);
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
        foreach ($this->_stores as $id => $code) {
            if (!$withDefault && $id == 0) {
                continue;
            }
            $storeData = ['id' => $id, 'code' => $code];
            $stores[$id] = new DataObject($storeData);
        }
        if (!$withDefault) {
            unset($stores[0]);
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
        /** @var AbstractAdapter $writer */
        $writer = $this->getMockForAbstractClass(
            AbstractAdapter::class,
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
        )->willReturnCallback(
            [$this, 'validateWriteRow']
        );

        $this->_model->setWriter($writer);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(AbstractModel::class);
        $arguments['data'] = $this->_customerData;
        $item = $this->getMockForAbstractClass(AbstractModel::class, $arguments);

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
