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
    protected $_websites = array(\Magento\Store\Model\Store::DEFAULT_STORE_ID => 'admin', 1 => 'website1');

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
    protected $_customerData = array(
        'id' => 1,
        'website_id' => 1,
        'store_id' => 1,
        'email' => '@email@domain.com',
        self::ATTRIBUTE_CODE => 1,
        'default_billing' => 1,
        'default_shipping' => 1
    );

    /**
     * Customer address data
     *
     * @var array
     */
    protected $_addressData = array('id' => 1, 'entity_id' => 1, 'parent_id' => 1, self::ATTRIBUTE_CODE => 1);

    /**
     * ObjectManager helper
     *
     * @var \Magento\TestFramework\Helper\ObjectManager
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

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $storeManager->expects(
            $this->once()
        )->method(
            'getWebsites'
        )->will(
            $this->returnCallback(array($this, 'getWebsites'))
        );

        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = new \Magento\CustomerImportExport\Model\Export\Address(
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
            $this->getMock(
                'Magento\CustomerImportExport\Model\Export\CustomerFactory',
                array(),
                array(),
                '',
                false
            ),
            $this->getMock('Magento\Customer\Model\Resource\Address\CollectionFactory', array(), array(), '', false),
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

        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);

        /** @var $attributeCollection \Magento\Framework\Data\Collection|PHPUnit_Framework_TestCase */
        $attributeCollection = $this->getMock(
            'Magento\Framework\Data\Collection',
            array('getEntityTypeCode'),
            array($entityFactory)
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

        $byPagesIterator = $this->getMock('stdClass', array('iterate'));
        $byPagesIterator->expects(
            $this->once()
        )->method(
            'iterate'
        )->will(
            $this->returnCallback(array($this, 'iterate'))
        );

        $customerCollection = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            array('addAttributeToSelect'),
            array(),
            '',
            false
        );

        $customerEntity = $this->getMock('stdClass', array('filterEntityCollection', 'setParameters'));
        $customerEntity->expects($this->any())->method('filterEntityCollection')->will($this->returnArgument(0));
        $customerEntity->expects($this->any())->method('setParameters')->will($this->returnSelf());

        $data = array(
            'translator' => $translator,
            'attribute_collection' => $attributeCollection,
            'page_size' => 1,
            'collection_by_pages_iterator' => $byPagesIterator,
            'entity_type_id' => 1,
            'customer_collection' => $customerCollection,
            'customer_entity' => $customerEntity,
            'address_collection' => 'not_used'
        );

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
     * Iterate stub
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Data\Collection\Db $collection
     * @param int $pageSize
     * @param array $callbacks
     */
    public function iterate(\Magento\Framework\Data\Collection\Db $collection, $pageSize, array $callbacks)
    {
        $resource = $this->getMock(
            'Magento\Customer\Model\Resource\Customer',
            array('getIdFieldName'),
            array(),
            '',
            false
        );
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
        $arguments = array(
            'data' => $this->_customerData,
            'resource' => $resource,
            $this->getMock('Magento\Customer\Model\Config\Share', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\AddressFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\Resource\Address\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\GroupFactory', array(), array(), '', false),
            $this->getMock('Magento\Customer\Model\AttributeFactory', array(), array(), '', false)
        );
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
        $this->_model->setParameters(array());

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
