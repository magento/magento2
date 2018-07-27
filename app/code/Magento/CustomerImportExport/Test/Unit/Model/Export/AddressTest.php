<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\Unit\Model\Export;

use Magento\CustomerImportExport\Model\Export\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
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
    protected $_websites = [\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1'];

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

    protected function setUp()
    {
        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback([$this, 'getWebsites'])
        );

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = new \Magento\CustomerImportExport\Model\Export\Address(
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
            $this->getMock(
                'Magento\CustomerImportExport\Model\Export\CustomerFactory',
                [],
                [],
                '',
                false
            ),
            $this->getMock('Magento\Customer\Model\ResourceModel\Address\CollectionFactory', [], [], '', false),
            $this->_getModelDependencies()
        );
    }

    protected function tearDown()
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
        $translator = $this->getMock('stdClass');

        $entityFactory = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);

        /** @var $attributeCollection \Magento\Framework\Data\Collection|\PHPUnit_Framework_TestCase */
        $attributeCollection = $this->getMock(
            'Magento\Framework\Data\Collection',
            ['getEntityTypeCode'],
            [$entityFactory]
        );
        $attributeCollection->expects(
            $this->once()
        )->method(
            'getEntityTypeCode'
        )->will(
            $this->returnValue('customer_address')
        );
        foreach ($this->_attributes as $attributeData) {
            $arguments = $this->_objectManager->getConstructArguments(
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

        $byPagesIterator = $this->getMock('stdClass', ['iterate']);
        $byPagesIterator->expects(
            $this->once()
        )->method(
            'iterate'
        )->will(
            $this->returnCallback([$this, 'iterate'])
        );

        $customerCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->setMethods(['addAttributeToSelect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customerEntity = $this->getMock('stdClass', ['filterEntityCollection', 'setParameters']);
        $customerEntity->expects($this->any())->method('filterEntityCollection')->will($this->returnArgument(0));
        $customerEntity->expects($this->any())->method('setParameters')->will($this->returnSelf());

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
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Framework\Data\Collection\AbstractDb $collection, $pageSize, array $callbacks)
    {
        $resource = $this->getMock(
            'Magento\Customer\Model\ResourceModel\Customer',
            ['getIdFieldName'],
            [],
            '',
            false
        );
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
        $arguments = [
            'data' => $this->_customerData,
            'resource' => $resource,
            $this->getMock('Magento\Customer\Model\Config\Share', [], [], '', false),
            $this->getMock('Magento\Customer\Model\AddressFactory', [], [], '', false),
            $this->getMock('Magento\Customer\Model\ResourceModel\Address\CollectionFactory', [], [], '', false),
            $this->getMock('Magento\Customer\Model\GroupFactory', [], [], '', false),
            $this->getMock('Magento\Customer\Model\AttributeFactory', [], [], '', false),
        ];
        /** @var $customer \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject */
        $customer = $this->_objectManager->getObject('Magento\Customer\Model\Customer', $arguments);

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
        $this->_model->setParameters([]);

        $arguments = $this->_objectManager->getConstructArguments('Magento\Framework\Model\AbstractModel');
        $arguments['data'] = $this->_addressData;
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
        $billingColumn = \Magento\CustomerImportExport\Model\Export\Address::COLUMN_NAME_DEFAULT_BILLING;
        $this->assertEquals($this->_customerData['default_billing'], $row[$billingColumn]);

        $shippingColumn = \Magento\CustomerImportExport\Model\Export\Address::COLUMN_NAME_DEFAULT_SHIPPING;
        $this->assertEquals($this->_customerData['default_shipping'], $row[$shippingColumn]);

        $idColumn = \Magento\CustomerImportExport\Model\Export\Address::COLUMN_ADDRESS_ID;
        $this->assertEquals($this->_addressData['id'], $row[$idColumn]);

        $emailColumn = \Magento\CustomerImportExport\Model\Export\Address::COLUMN_EMAIL;
        $this->assertEquals($this->_customerData['email'], $row[$emailColumn]);

        $websiteColumn = \Magento\CustomerImportExport\Model\Export\Address::COLUMN_WEBSITE;
        $this->assertEquals($this->_websites[$this->_customerData['website_id']], $row[$websiteColumn]);

        $this->assertEquals($this->_addressData[self::ATTRIBUTE_CODE], $row[self::ATTRIBUTE_CODE]);
    }
}
