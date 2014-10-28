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

use Magento\Customer\Service\V1\Data\Address;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Customer\Service\V1\Data\RegionBuilder;
use Magento\Framework\Service\Data\AttributeValue;
use Magento\Framework\Service\Data\AttributeValueBuilder;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */
    const ID = 14;

    const IS_SHIPPING = true;

    const IS_BILLING = false;

    const COMPANY = 'Company Name';

    const FAX = '(555) 555-5555';

    const MIDDLENAME = 'Mid';

    const PREFIX = 'Mr.';

    const SUFFIX = 'Esq.';

    const VAT_ID = 'S45';

    const FIRSTNAME = 'Jane';

    const LASTNAME = 'Doe';

    const STREET_LINE_0 = '7700 W Parmer Ln';

    const CITY = 'Austin';

    const COUNTRY_CODE = 'US';

    const POSTCODE = '78620';

    const TELEPHONE = '5125125125';

    const REGION = 'Texas';

    protected $_expectedValues = array(
        'id' => 14,
        'default_shipping' => true,
        'default_billing' => false,
        'company' => 'Company Name',
        'fax' => '(555) 555-5555',
        'middlename' => 'Mid',
        'prefix' => 'Mr.',
        'suffix' => 'Esq.',
        'vat_id' => 'S45',
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'street' => array('7700 W Parmer Ln'),
        'city' => 'Austin',
        'country_id' => 'US',
        'postcode' => '78620',
        'telephone' => '5125125125',
        'region' => array('region_id' => 0, 'region' => 'Texas')
    );

    /**
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder
     */
    private $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataService */
    private $_customerMetadataService;

    /** @var \Magento\Framework\Service\Data\AttributeValueBuilder */
    private $_valueBuilder;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $regionBuilder = $this->objectManagerHelper->getObject('Magento\Customer\Service\V1\Data\RegionBuilder');
        /** @var \Magento\Customer\Service\V1\CustomerMetadataService $customerMetadataService */
        $this->_customerMetadataService = $this->getMockBuilder(
            'Magento\Customer\Service\V1\AddressMetadataService'
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
        $this->_valueBuilder = $this->objectManagerHelper
            ->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
        $this->_addressBuilder = $this->objectManagerHelper->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'regionBuilder' => $regionBuilder,
                'metadataService' => $this->_customerMetadataService
            ]
        );
    }

    public function testMinimalAddress()
    {
        $this->_fillMinimumRequiredFields($this->_addressBuilder);
        $this->_assertMinimumRequiredFields($this->_addressBuilder->create());
    }

    public function testCopyAndModify()
    {
        /** @var \Magento\Customer\Service\V1\Data\Address $origAddress */
        $origAddress = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Address'
        )->disableOriginalConstructor()->getMock();
        $this->_mockReturnValue(
            $origAddress,
            array(
                'getFirstname' => $this->_expectedValues['firstname'],
                'getLastname' => $this->_expectedValues['lastname'],
                'getStreet' => $this->_expectedValues['street'],
                'getCity' => $this->_expectedValues['city'],
                'getCountryId' => $this->_expectedValues['country_id'],
                'getRegion' => $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
                        ->setRegionId(0)->setRegion('Texas')->create(),
                'getPostcode' => $this->_expectedValues['postcode'],
                'getTelephone' => $this->_expectedValues['telephone']
            )
        );

        $this->_assertMinimumRequiredFields($origAddress);
    }

    public function testFullAddress()
    {
        $this->_fillAllFields($this->_addressBuilder);
        $address = $this->_addressBuilder->create();

        $this->_assertMinimumRequiredFields($address);
        $this->assertEquals($this->_expectedValues['id'], $address->getId());
        $this->assertEquals($this->_expectedValues['default_shipping'], $address->isDefaultShipping());
        $this->assertEquals($this->_expectedValues['default_billing'], $address->isDefaultBilling());
        $this->assertEquals($this->_expectedValues['company'], $address->getCompany());
        $this->assertEquals($this->_expectedValues['fax'], $address->getFax());
        $this->assertEquals($this->_expectedValues['middlename'], $address->getMiddlename());
        $this->assertEquals($this->_expectedValues['prefix'], $address->getPrefix());
        $this->assertEquals($this->_expectedValues['suffix'], $address->getSuffix());
        $this->assertEquals($this->_expectedValues['vat_id'], $address->getVatId());
    }

    public function testSetStreet()
    {
        $this->_fillMinimumRequiredFields($this->_addressBuilder);
        $tmpAddress = $this->_addressBuilder->create();
        $street = $tmpAddress->getStreet();
        $street[] = 'Line_1';
        $this->_addressBuilder->populate($tmpAddress);
        $this->_addressBuilder->setStreet($street);

        $address = $this->_addressBuilder->create();
        $this->_assertMinimumRequiredFields($address);
        $this->assertEquals('Line_1', $address->getStreet()[1]);
    }

    public function testGetAttributes()
    {
        $this->_fillAllFields($this->_addressBuilder);
        $expected = $this->_expectedValues;
        $this->assertEquals($expected, $this->_addressBuilder->create()->__toArray());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }

    /**
     * @param AddressBuilder $addressBuilder
     */
    private function _fillMinimumRequiredFields(AddressBuilder $addressBuilder)
    {
        $addressBuilder->setFirstname($this->_expectedValues['firstname']);
        $addressBuilder->setLastname($this->_expectedValues['lastname']);
        $addressBuilder->setStreet($this->_expectedValues['street']);
        $addressBuilder->setCity($this->_expectedValues['city']);
        $addressBuilder->setCountryId($this->_expectedValues['country_id']);
        $addressBuilder->setRegion(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')->setRegionId(
                $this->_expectedValues['region']['region_id']
            )->setRegion(
                $this->_expectedValues['region']['region']
            )->create()
        );
        $addressBuilder->setPostcode($this->_expectedValues['postcode']);
        $addressBuilder->setTelephone($this->_expectedValues['telephone']);
    }

    /**
     * @param AddressBuilder $addressBuilder
     */
    private function _fillAllFields(AddressBuilder $addressBuilder)
    {
        $this->_fillMinimumRequiredFields($addressBuilder);

        $addressBuilder->setId($this->_expectedValues['id']);
        $addressBuilder->setSuffix($this->_expectedValues['suffix']);
        $addressBuilder->setMiddlename($this->_expectedValues['middlename']);
        $addressBuilder->setPrefix($this->_expectedValues['prefix']);
        $addressBuilder->setVatId($this->_expectedValues['vat_id']);
        $addressBuilder->setDefaultShipping($this->_expectedValues['default_shipping']);
        $addressBuilder->setDefaultBilling($this->_expectedValues['default_billing']);
        $addressBuilder->setCompany($this->_expectedValues['company']);
        $addressBuilder->setFax($this->_expectedValues['fax']);
    }

    /**
     * @param Address $address
     */
    private function _assertMinimumRequiredFields(Address $address)
    {
        $this->assertEquals($this->_expectedValues['firstname'], $address->getFirstname());
        $this->assertEquals($this->_expectedValues['lastname'], $address->getLastname());
        $this->assertEquals($this->_expectedValues['street'][0], $address->getStreet()[0]);
        $this->assertEquals($this->_expectedValues['city'], $address->getCity());
        $this->assertEquals($this->_expectedValues['country_id'], $address->getCountryId());
        $this->assertEquals(
            $this->objectManagerHelper->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')->setRegionId(
                $this->_expectedValues['region']['region_id']
            )->setRegion(
                $this->_expectedValues['region']['region']
            )->create(),
            $address->getRegion()
        );
        $this->assertEquals($this->_expectedValues['postcode'], $address->getPostcode());
        $this->assertEquals($this->_expectedValues['telephone'], $address->getTelephone());
    }

    public function testSetCustomAttribute()
    {
        $this->_addressBuilder->populateWithArray(array());
        $address = $this->_addressBuilder->setCustomAttribute(
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

        $address = $this->_addressBuilder
            ->setCustomAttributes([$attributeValue1, $attributeValue2])
            ->create();

        $this->assertEquals('78777', $address->getCustomAttribute('warehouse_zip')->getValue());
        $this->assertEquals('90051', $address->getCustomAttribute('warehouse_alternate')->getValue());
        $this->assertEquals($customerAttributes, $address->__toArray()[Customer::CUSTOM_ATTRIBUTES_KEY]);
    }
}
