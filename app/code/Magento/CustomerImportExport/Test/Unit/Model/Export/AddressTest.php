<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Export;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\CustomerImportExport\Model\Export\Address;
use Magento\CustomerImportExport\Model\Export\CustomerFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /**
     * Test attribute code
     */
    const ATTRIBUTE_CODE = 'code1';

    /**
     * Websites array (website id => code)
     *
     * @var array
     */
    protected $_websites = [Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1'];

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
    protected $_customerData = [
        'id' => 1,
        'website_id' => 1,
        'store_id' => 1,
        'email' => '@email@domain.com',
        self::ATTRIBUTE_CODE => 1,
        'default_billing' => 1,
        'default_shipping' => 1,
    ];

    /**
     * Customer address data
     *
     * @var array
     */
    protected $_addressData = ['id' => 1, 'entity_id' => 1, 'parent_id' => 1, self::ATTRIBUTE_CODE => 1];

    /**
     * ObjectManager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * Customer address export model
     *
     * @var Address
     */
    protected $_model;

    protected function setUp(): void
    {
        $storeManager = $this->createMock(StoreManager::class);
        $storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->willReturnCallback(
            [$this, 'getWebsites']
        );

        $this->_objectManager = new ObjectManager($this);
        $this->_model = new Address(
            $this->getMockForAbstractClass(ScopeConfigInterface::class),
            $storeManager,
            $this->createMock(Factory::class),
            $this->createMock(CollectionByPagesIteratorFactory::class),
            $this->getMockForAbstractClass(TimezoneInterface::class),
            $this->createMock(Config::class),
            $this->createMock(CollectionFactory::class),
            $this->createMock(CustomerFactory::class),
            $this->createMock(\Magento\Customer\Model\ResourceModel\Address\CollectionFactory::class),
            $this->_getModelDependencies()
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_objectManager);
    }

    /**
     * Create mocks for all $this->_model dependencies
     *
     * @return array
     */
    protected function _getModelDependencies()
    {
        $translator = $this->createMock(\stdClass::class);

        $entityFactory = $this->createMock(EntityFactory::class);

        /** @var Collection|TestCase $attributeCollection */
        $attributeCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['getEntityTypeCode'])
            ->setConstructorArgs([$entityFactory])
            ->getMock();

        $attributeCollection->expects(
            $this->once()
        )->method(
            'getEntityTypeCode'
        )->willReturn(
            'customer_address'
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $this->_objectManager->getConstructArguments(
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

        $byPagesIterator = $this->getMockBuilder(\stdClass::class)->addMethods(['iterate'])
            ->disableOriginalConstructor()
            ->getMock();
        $byPagesIterator->expects(
            $this->once()
        )->method(
            'iterate'
        )->willReturnCallback(
            [$this, 'iterate']
        );

        $customerCollection = $this->getMockBuilder(AbstractDb::class)
            ->setMethods(['addAttributeToSelect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customerEntity = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['filterEntityCollection', 'setParameters'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerEntity->expects($this->any())->method('filterEntityCollection')->willReturnArgument(0);
        $customerEntity->expects($this->any())->method('setParameters')->willReturnSelf();

        $data = [
            'translator' => $translator,
            'attribute_collection' => $attributeCollection,
            'page_size' => 1,
            'collection_by_pages_iterator' => $byPagesIterator,
            'entity_type_id' => 1,
            'customer_collection' => $customerCollection,
            'customer_entity' => $customerEntity,
            'address_collection' => 'not_used',
        ];

        return $data;
    }

    /**
     * Get websites stub
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
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param AbstractDb $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(AbstractDb $collection, $pageSize, array $callbacks)
    {
        $resource = $this->createPartialMock(Customer::class, ['getIdFieldName']);
        $resource->expects($this->any())->method('getIdFieldName')->willReturn('id');
        $arguments = [
            'data' => $this->_customerData,
            'resource' => $resource,
            $this->createMock(Share::class),
            $this->createMock(AddressFactory::class),
            $this->createMock(\Magento\Customer\Model\ResourceModel\Address\CollectionFactory::class),
            $this->createMock(GroupFactory::class),
            $this->createMock(\Magento\Customer\Model\AttributeFactory::class),
        ];
        /** @var $customer \Magento\Customer\Model\Customer|MockObject */
        $customer = $this->_objectManager->getObject(\Magento\Customer\Model\Customer::class, $arguments);

        foreach ($callbacks as $callback) {
            call_user_func($callback, $customer);
        }
    }

    /**
     * Test for method exportItem()
     *
     * @covers \Magento\CustomerImportExport\Model\Export\Address::exportItem
     */
    public function testExportItem()
    {
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
        $this->_model->setParameters([]);

        $arguments = $this->_objectManager->getConstructArguments(AbstractModel::class);
        $arguments['data'] = $this->_addressData;
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
        $billingColumn = Address::COLUMN_NAME_DEFAULT_BILLING;
        $this->assertEquals($this->_customerData['default_billing'], $row[$billingColumn]);

        $shippingColumn = Address::COLUMN_NAME_DEFAULT_SHIPPING;
        $this->assertEquals($this->_customerData['default_shipping'], $row[$shippingColumn]);

        $idColumn = Address::COLUMN_ADDRESS_ID;
        $this->assertEquals($this->_addressData['id'], $row[$idColumn]);

        $emailColumn = Address::COLUMN_EMAIL;
        $this->assertEquals($this->_customerData['email'], $row[$emailColumn]);

        $websiteColumn = Address::COLUMN_WEBSITE;
        $this->assertEquals($this->_websites[$this->_customerData['website_id']], $row[$websiteColumn]);

        $this->assertEquals($this->_addressData[self::ATTRIBUTE_CODE], $row[self::ATTRIBUTE_CODE]);
    }
}
