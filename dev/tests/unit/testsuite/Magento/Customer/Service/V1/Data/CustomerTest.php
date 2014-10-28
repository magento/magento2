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
namespace Magento\Customer\Service\V1\Data;

use Magento\Framework\Service\Data\AttributeValue;

/**
 * Customer
 *
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    const CONFIRMATION = 'a4fg7h893e39d';

    const CREATED_AT = '2013-11-05';

    const STORE_NAME = 'Store Name';

    const DOB = '1970-01-01';

    const GENDER = 'Male';

    const GROUP_ID = 1;

    const MIDDLENAME = 'A';

    const PREFIX = 'Mr.';

    const STORE_ID = 1;

    const SUFFIX = 'Esq.';

    const TAXVAT = '12';

    const WEBSITE_ID = 1;

    /** Sample values for testing */
    const ID = 1;

    const FIRSTNAME = 'Jane';

    const LASTNAME = 'Doe';

    const NAME = 'J';

    const EMAIL = 'janedoe@example.com';

    const ATTRIBUTE_CODE = 'attribute_code';

    const ATTRIBUTE_VALUE = 'attribute_value';

    /** @var  \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    protected $_customerBuilder;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $customerMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
            array(),
            '',
            false
        );
        $customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomAttributesMetadata'
        )->will(
            $this->returnValue([
                new \Magento\Framework\Object(['attribute_code' => 'zip']),
                new \Magento\Framework\Object(['attribute_code' => 'locale'])
            ])
        );
        $valueBuilder = $this->_objectManager->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
        $this->_customerBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'valueBuilder' => $valueBuilder,
                'metadataService' => $customerMetadataService
            ]
        );
    }

    public function testSetters()
    {
        $customerData = $this->_createCustomerData();
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();
        $this->assertEquals(self::ID, $customer->getId());
        $this->assertEquals(self::FIRSTNAME, $customer->getFirstname());
        $this->assertEquals(self::LASTNAME, $customer->getLastname());
        $this->assertEquals(self::EMAIL, $customer->getEmail());
        $this->assertEquals(self::CONFIRMATION, $customer->getConfirmation());
        $this->assertEquals(self::CREATED_AT, $customer->getCreatedAt());
        $this->assertEquals(self::STORE_NAME, $customer->getCreatedIn());
        $this->assertEquals(self::DOB, $customer->getDob());
        $this->assertEquals(self::GENDER, $customer->getGender());
        $this->assertEquals(self::GROUP_ID, $customer->getGroupId());
        $this->assertEquals(self::MIDDLENAME, $customer->getMiddlename());
        $this->assertEquals(self::PREFIX, $customer->getPrefix());
        $this->assertEquals(self::STORE_ID, $customer->getStoreId());
        $this->assertEquals(self::SUFFIX, $customer->getSuffix());
        $this->assertEquals(self::TAXVAT, $customer->getTaxvat());
        $this->assertEquals(self::WEBSITE_ID, $customer->getWebsiteId());
    }

    public function testGetAttributes()
    {
        $customerData = $this->_createCustomerData();
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();

        $actualAttributes = \Magento\Framework\Convert\ConvertArray::toFlatArray($customer->__toArray());
        $this->assertEquals(
            array(
                'id' => self::ID,
                'confirmation' => self::CONFIRMATION,
                'created_at' => self::CREATED_AT,
                'created_in' => self::STORE_NAME,
                'dob' => self::DOB,
                'email' => self::EMAIL,
                'firstname' => self::FIRSTNAME,
                'gender' => self::GENDER,
                'group_id' => self::GROUP_ID,
                'lastname' => self::LASTNAME,
                'middlename' => self::MIDDLENAME,
                'prefix' => self::PREFIX,
                'store_id' => self::STORE_ID,
                'suffix' => self::SUFFIX,
                'taxvat' => self::TAXVAT,
                'website_id' => self::WEBSITE_ID
            ),
            $actualAttributes
        );
    }

    public function testInvalidCustomAttributes()
    {
        $customAttributes= [
            'custom_attribute1' => [
                AttributeValue::ATTRIBUTE_CODE => 'custom_attribute1',
                AttributeValue::VALUE => 'value1'
            ],
            'custom_attribute2' => [
                AttributeValue::ATTRIBUTE_CODE => 'custom_attribute1',
                AttributeValue::VALUE => 'value2'
            ]
        ];
        $customerData = array('attribute1' => 'value1', Customer::CUSTOM_ATTRIBUTES_KEY => $customAttributes);
        $customerDataObject = $this->_customerBuilder->populateWithArray($customerData)->create();
        $this->assertEquals(
            [],
            $customerDataObject->getCustomAttributes(),
            'Unexpected custom attributes.'
        );
    }

    public function testGetCustomAttributes()
    {
        $customAttributes= [
            'zip' => [
                AttributeValue::ATTRIBUTE_CODE => 'zip',
                AttributeValue::VALUE => 'value1'
            ],
            'locale' => [
                AttributeValue::ATTRIBUTE_CODE => 'locale',
                AttributeValue::VALUE => 'value2'
            ]
        ];
        $customerData = array('attribute1' => 'value1', Customer::CUSTOM_ATTRIBUTES_KEY => $customAttributes);
        $customerDataObject = $this->_customerBuilder->populateWithArray($customerData)->create();
        foreach ($customerDataObject->getCustomAttributes() as $attributeValue) {
            $this->assertEquals(
                $customAttributes[$attributeValue->getAttributeCode()][AttributeValue::VALUE],
                $attributeValue->getValue()
            );
        }
    }

    public function testPopulateFromPrototypeVsArray()
    {
        $customerFromArray = $this->_customerBuilder->populateWithArray(
            array(
                Customer::FIRSTNAME => self::FIRSTNAME,
                Customer::LASTNAME => self::LASTNAME,
                Customer::EMAIL => self::EMAIL,
                Customer::ID => self::ID,
                'entity_id' => self::ID
            )
        )->create();
        $customerFromPrototype = $this->_customerBuilder->populate($customerFromArray)->create();

        $this->assertEquals($customerFromArray->__toArray(), $customerFromPrototype->__toArray());
    }

    public function testPopulateFromCustomerIdInArray()
    {
        $customer = $this->_customerBuilder->populateWithArray(
            array(
                Customer::FIRSTNAME => self::FIRSTNAME,
                Customer::LASTNAME => self::LASTNAME,
                Customer::EMAIL => self::EMAIL,
                Customer::ID => self::ID
            )
        )->create();

        $this->assertEquals(self::FIRSTNAME, $customer->getFirstname());
        $this->assertEquals(self::LASTNAME, $customer->getLastname());
        $this->assertEquals(self::EMAIL, $customer->getEmail());
        $this->assertEquals(self::ID, $customer->getId());
    }

    /**
     * Create customer using setters.
     *
     * @return array
     */
    private function _createCustomerData()
    {
        return array(
            self::ATTRIBUTE_CODE => self::ATTRIBUTE_VALUE,
            'id' => self::ID,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'email' => self::EMAIL,
            'confirmation' => self::CONFIRMATION,
            'created_at' => self::CREATED_AT,
            'created_in' => self::STORE_NAME,
            'dob' => self::DOB,
            'gender' => self::GENDER,
            'group_id' => self::GROUP_ID,
            'middlename' => self::MIDDLENAME,
            'prefix' => self::PREFIX,
            'store_id' => self::STORE_ID,
            'suffix' => self::SUFFIX,
            'taxvat' => self::TAXVAT,
            'website_id' => self::WEBSITE_ID
        );
    }
}
