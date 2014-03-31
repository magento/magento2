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

use Magento\Customer\Service\V1\CustomerMetadataService;
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder;
use Magento\Service\Data\AbstractObject;
use Magento\Service\Data\AbstractObjectBuilder;

class CustomerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder|\PHPUnit_Framework_TestCase */
    protected $_customerBuilder;

    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataService */
    private $_customerMetadataService;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Customer\Service\V1\CustomerMetadataService $customerMetadataService */
        $this->_customerMetadataService = $this->getMockBuilder(
            'Magento\Customer\Service\V1\CustomerMetadataService'
        )->setMethods(
            array('getCustomCustomerAttributeMetadata')
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomCustomerAttributeMetadata'
        )->will(
            $this->returnValue(
                array(
                    new \Magento\Object(array('attribute_code' => 'warehouse_zip')),
                    new \Magento\Object(array('attribute_code' => 'warehouse_alternate'))
                )
            )
        );
        $this->_customerBuilder = new CustomerBuilder($this->_customerMetadataService);
        parent::setUp();
    }

    public function testMergeDataObjects()
    {
        $firstname1 = 'Firstname1';
        $lastnam1 = 'Lastname1';
        $email1 = 'email1@example.com';
        $firstDataObject = $this->_customerBuilder->setFirstname(
            $firstname1
        )->setLastname(
            $lastnam1
        )->setEmail(
            $email1
        )->create();

        $lastname2 = 'Lastname2';
        $middlename2 = 'Middlename2';
        $secondDataObject = $this->_customerBuilder->setLastname($lastname2)->setMiddlename($middlename2)->create();

        $mergedDataObject = $this->_customerBuilder->mergeDataObjects($firstDataObject, $secondDataObject);
        $this->assertNotSame(
            $firstDataObject,
            $mergedDataObject,
            'A new object must be created for merged Data Object.'
        );
        $this->assertNotSame(
            $secondDataObject,
            $mergedDataObject,
            'A new object must be created for merged Data Object.'
        );
        $expectedDataObject = array(
            'firstname' => $firstname1,
            'lastname' => $lastname2,
            'middlename' => $middlename2,
            'email' => $email1
        );
        $this->assertEquals(
            $expectedDataObject,
            $mergedDataObject->__toArray(),
            'Data Objects were merged incorrectly.'
        );
    }

    public function testMergeDataObjectsWitArray()
    {
        $firstname1 = 'Firstname1';
        $lastnam1 = 'Lastname1';
        $email1 = 'email1@example.com';
        $firstDataObject = $this->_customerBuilder->setFirstname(
            $firstname1
        )->setLastname(
            $lastnam1
        )->setEmail(
            $email1
        )->create();

        $lastname2 = 'Lastname2';
        $middlename2 = 'Middlename2';
        $dataForMerge = array('lastname' => $lastname2, 'middlename' => $middlename2);

        $mergedDataObject = $this->_customerBuilder->mergeDataObjectWithArray($firstDataObject, $dataForMerge);
        $this->assertNotSame(
            $firstDataObject,
            $mergedDataObject,
            'A new object must be created for merged Data Object.'
        );
        $expectedDataObject = array(
            'firstname' => $firstname1,
            'lastname' => $lastname2,
            'middlename' => $middlename2,
            'email' => $email1
        );
        $this->assertEquals(
            $expectedDataObject,
            $mergedDataObject->__toArray(),
            'Data Object with array were merged incorrectly.'
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Wrong prototype object given. It can only be of
     * "Magento\Customer\Service\V1\Data\Customer" type.
     */
    // @codingStandardsIgnoreEnd
    public function testPopulateException()
    {
        $addressData = (new AddressBuilder(new RegionBuilder(), $this->_customerMetadataService))->create();
        $this->_customerBuilder->populate($addressData);
    }

    public function testPopulate()
    {
        $email = 'test@example.com';
        $customerBuilder1 = new CustomerBuilder($this->_customerMetadataService);
        $customerBuilder2 = new CustomerBuilder($this->_customerMetadataService);
        $customer = $customerBuilder1->setEmail($email)->create();
        $customerBuilder2->setFirstname('fname')->setLastname('lname')->create();
        //Make sure email is not populated as yet
        $this->assertEquals(null, $customerBuilder2->create()->getEmail());
        $customerBuilder2->populate($customer);
        //Verify if email is set correctly
        $this->assertEquals($email, $customerBuilder2->create()->getEmail());
    }

    public function testPopulateWithArray()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace'
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();
        $expectedData = array('email' => 'test@example.com', 'firstname' => 'John', 'lastname' => 'Doe');
        $this->assertEquals($expectedData, $customer->__toArray());
    }

    public function testPopulateWithArrayCustomAttributes()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();

        $expectedData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            Customer::CUSTOM_ATTRIBUTES_KEY => array('warehouse_zip' => '78777', 'warehouse_alternate' => '90051')
        );
        $this->assertEquals($expectedData, $customer->__toArray());
    }

    public function testSetCustomAttribute()
    {
        $customer = $this->_customerBuilder->setCustomAttribute(
            'warehouse_zip',
            '78777'
        )->setCustomAttribute(
            'warehouse_alternate',
            '90051'
        )->create();
        $this->assertEquals('78777', $customer->getCustomAttribute('warehouse_zip'));
        $this->assertEquals('90051', $customer->getCustomAttribute('warehouse_alternate'));

        $customAttributes = array(
            Customer::CUSTOM_ATTRIBUTES_KEY => array('warehouse_zip' => '78777', 'warehouse_alternate' => '90051')
        );
        $this->assertEquals($customAttributes[Customer::CUSTOM_ATTRIBUTES_KEY], $customer->getCustomAttributes());
        $this->assertEquals($customAttributes, $customer->__toArray());
    }

    public function testSetCustomAttributes()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $expectedData = array(
            Customer::CUSTOM_ATTRIBUTES_KEY => array('warehouse_zip' => '78777', 'warehouse_alternate' => '90051')
        );
        $customer = $this->_customerBuilder->setCustomAttributes($customerData)->create();

        $this->assertEquals('78777', $customer->getCustomAttribute('warehouse_zip'));
        $this->assertEquals('90051', $customer->getCustomAttribute('warehouse_alternate'));
        $this->assertEquals($expectedData[Customer::CUSTOM_ATTRIBUTES_KEY], $customer->getCustomAttributes());
        $this->assertEquals($expectedData, $customer->__toArray());
    }

    public function testMergeDataObjectWithArrayCustomData()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $expectedData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            Customer::CUSTOM_ATTRIBUTES_KEY => array('warehouse_zip' => '78666', 'warehouse_alternate' => '90051')
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();
        $customer2 = $this->_customerBuilder->mergeDataObjectWithArray(
            $customer,
            array('unknown_key' => 'Golden Necklace', 'warehouse_zip' => '78666', 'lastname' => 'Johnson')
        );
        $this->assertEquals('78666', $customer2->getCustomAttribute('warehouse_zip'));
        $this->assertEquals('90051', $customer2->getCustomAttribute('warehouse_alternate'));
        $this->assertEquals($expectedData[Customer::CUSTOM_ATTRIBUTES_KEY], $customer2->getCustomAttributes());
        $this->assertEquals($expectedData, $customer2->__toArray());
    }

    public function testMergeDataObjectsCustomData()
    {
        $customer1Data = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $customer2Data = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78666',
            'warehouse_alternate' => '90051'
        );
        $expectedData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            Customer::CUSTOM_ATTRIBUTES_KEY => array('warehouse_zip' => '78666', 'warehouse_alternate' => '90051')
        );
        $customer1 = $this->_customerBuilder->populateWithArray($customer1Data)->create();
        $customer2 = $this->_customerBuilder->populateWithArray($customer2Data)->create();
        $customer3 = $this->_customerBuilder->mergeDataObjects($customer1, $customer2);
        $this->assertEquals('78666', $customer3->getCustomAttribute('warehouse_zip'));
        $this->assertEquals('90051', $customer3->getCustomAttribute('warehouse_alternate'));
        $this->assertEquals($expectedData[Customer::CUSTOM_ATTRIBUTES_KEY], $customer3->getCustomAttributes());
        $this->assertEquals($expectedData, $customer3->__toArray());
    }

    public function testToFlatArray()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $expectedCustomerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();

        $this->assertEquals($expectedCustomerData, \Magento\Service\DataObjectConverter::toFlatArray($customer));
    }
}
