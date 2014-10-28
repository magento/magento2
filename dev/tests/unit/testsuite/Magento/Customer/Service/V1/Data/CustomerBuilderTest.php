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
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder;
use Magento\Framework\Service\Data\AbstractExtensibleObject;
use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

class CustomerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder|\PHPUnit_Framework_TestCase */
    protected $_customerBuilder;

    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataService */
    private $_customerMetadataService;

    /** @var \Magento\Customer\Service\V1\AddressMetadataService */
    private $_addressMetadataService;

    /** @var \Magento\Framework\Service\Data\AttributeValueBuilder */
    private $_valueBuilder;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Customer\Service\V1\CustomerMetadataService $customerMetadataService */
        $this->_customerMetadataService = $this->getMockBuilder(
            'Magento\Customer\Service\V1\CustomerMetadataService'
        )->setMethods(
            array('getCustomAttributesMetadata')
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomAttributesMetadata'
        )->will(
            $this->returnValue(
                array(
                    new \Magento\Framework\Object(array('attribute_code' => 'warehouse_zip')),
                    new \Magento\Framework\Object(array('attribute_code' => 'warehouse_alternate'))
                )
            )
        );
        $this->_addressMetadataService = $this->getMockBuilder(
            'Magento\Customer\Service\V1\AddressMetadataService'
        )->setMethods(
                array('getCustomAttributesMetadata')
            )->disableOriginalConstructor()->getMock();
        $this->_addressMetadataService->expects(
            $this->any()
        )->method(
                'getCustomAttributesMetadata'
            )->will(
                $this->returnValue(
                    array(
                        new \Magento\Framework\Object(array('attribute_code' => 'warehouse_zip')),
                        new \Magento\Framework\Object(array('attribute_code' => 'warehouse_alternate'))
                    )
                )
            );
        $this->_valueBuilder = $this->_objectManager->getObject(
            'Magento\Framework\Service\Data\AttributeValueBuilder'
        );
        $this->_customerBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'metadataService' => $this->_customerMetadataService
            ]
        );
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
        $addressData = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'regionBuilder' => $this->_objectManager->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder'),
                'metadataService' => $this->_addressMetadataService
            ]
        )->create();
        $this->_customerBuilder->populate($addressData);
    }

    public function testPopulate()
    {
        $email = 'test@example.com';
        $customerBuilder1 = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'metadataService' => $this->_customerMetadataService
            ]
        );
        $customerBuilder2 = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'metadataService' => $this->_customerMetadataService
            ]
        );
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
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78777'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();
        unset($customerData['unknown_key']);
        $this->assertEquals($customerData, $customer->__toArray());
    }

    public function testSetCustomAttribute()
    {
        $this->_customerBuilder->populateWithArray(array());
        $address = $this->_customerBuilder->setCustomAttribute(
            'warehouse_zip',
            '78777'
        )->setCustomAttribute(
                'warehouse_alternate',
                '90051'
            )->create();
        $this->assertEquals('78777', $address->getCustomAttribute('warehouse_zip')->getValue());
        $this->assertEquals('90051', $address->getCustomAttribute('warehouse_alternate')->getValue());

        foreach ($address->getCustomAttributes() as $customAttribute) {
            $attributes[Customer::CUSTOM_ATTRIBUTES_KEY][$customAttribute->getAttributeCode()] = [
                AttributeValue::ATTRIBUTE_CODE => $customAttribute->getAttributeCode(),
                AttributeValue::VALUE => $customAttribute->getValue()
            ];
        }
        $this->assertEquals($attributes, $address->__toArray());
    }

    public function testSetCustomAttributes()
    {

        $customerAttributes = [
            'warehouse_zip' => [
                AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                AttributeValue::VALUE => '78777'
            ],
            'warehouse_alternate' => [
                AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                AttributeValue::VALUE => '90051'
            ]
        ];

        $attributeValue1 = $this->_valueBuilder
            ->populateWithArray($customerAttributes['warehouse_zip'])
            ->create();
        $attributeValue2 = $this->_valueBuilder
            ->populateWithArray($customerAttributes['warehouse_alternate'])
            ->create();

        $address = $this->_customerBuilder->setCustomAttributes([$attributeValue1, $attributeValue2])
            ->create();

        $this->assertEquals('78777', $address->getCustomAttribute('warehouse_zip')->getValue());
        $this->assertEquals('90051', $address->getCustomAttribute('warehouse_alternate')->getValue());
        $this->assertEquals($customerAttributes, $address->__toArray()[Customer::CUSTOM_ATTRIBUTES_KEY]);
    }

    public function testToArrayCustomAttributes()
    {
        $customAttributes = [
            'warehouse_zip' => [
                AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                AttributeValue::VALUE => '78777'
            ],
            'warehouse_alternate' => [
                AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                AttributeValue::VALUE => '90051'
            ]
        ];
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            Customer::CUSTOM_ATTRIBUTES_KEY => $customAttributes
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();
        $this->assertEquals(
            $customAttributes,
            $customer->__toArray()[Customer::CUSTOM_ATTRIBUTES_KEY]
        );
    }

    public function testMergeDataObjectWithArrayCustomData()
    {
        $customerData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78777'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );
        $customer = $this->_customerBuilder->populateWithArray($customerData)->create();

        $customer2 = $this->_customerBuilder->mergeDataObjectWithArray(
            $customer,
            [
                'lastname' => 'Johnson',
                'unknown_key' => 'Golden Necklace',
                Customer::CUSTOM_ATTRIBUTES_KEY => [
                    'warehouse_zip' => [
                        AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                        AttributeValue::VALUE => '78666'
                    ],
                    'warehouse_alternate' => [
                        AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                        AttributeValue::VALUE => '90051'
                    ]
                ]
            ]
        );

        $expectedData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78666'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );

        $this->assertEquals('78666', $customer2->getCustomAttribute('warehouse_zip')->getValue());
        $this->assertEquals('90051', $customer2->getCustomAttribute('warehouse_alternate')->getValue());
        foreach ($customer2->getCustomAttributes() as $customAttribute) {
            $this->assertEquals(
                $expectedData[Customer::CUSTOM_ATTRIBUTES_KEY][$customAttribute->getAttributeCode()]['value'],
                $customAttribute->getValue()
            );
        }
        $this->assertEquals($expectedData, $customer2->__toArray());
    }

    public function testMergeDataObjectsCustomData()
    {
        $customer1Data = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78777'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );
        $customer2Data = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            'unknown_key' => 'Golden Necklace',
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78666'
                ]
            ]
        );
        $expectedData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Johnson',
            Customer::CUSTOM_ATTRIBUTES_KEY => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78666'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );
        $customer1 = $this->_customerBuilder->populateWithArray($customer1Data)->create();
        $customer2 = $this->_customerBuilder->populateWithArray($customer2Data)->create();
        $customer3 = $this->_customerBuilder->mergeDataObjects($customer1, $customer2);
        $this->assertEquals('78666', $customer3->getCustomAttribute('warehouse_zip')->getValue());
        $this->assertEquals('90051', $customer3->getCustomAttribute('warehouse_alternate')->getValue());
        foreach ($customer3->getCustomAttributes() as $customAttribute) {
            $this->assertEquals(
                $expectedData[Customer::CUSTOM_ATTRIBUTES_KEY][$customAttribute->getAttributeCode()]['value'],
                $customAttribute->getValue()
            );
        }
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

        $this->assertEquals(
            $expectedCustomerData,
            \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customer)
        );
    }
}
