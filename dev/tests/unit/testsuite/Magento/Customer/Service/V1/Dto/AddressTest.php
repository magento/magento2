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

namespace Magento\Customer\Service\V1\Dto;

use Magento\Customer\Service\V1\Dto\Address;
use Magento\Customer\Service\V1\Dto\AddressBuilder;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    private $_expectedValues = [
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
        'street' => ['7700 W Parmer Ln'],
        'city' => 'Austin',
        'country_id' => 'US',
        'postcode' => '78620',
        'telephone' => '5125125125',
        'region' => [
            'region_id' => 0,
            'region' => 'Texas',
        ],
    ];

    /**
     * @var \Magento\Customer\Service\V1\Dto\AddressBuilder
     */
    private $_addressBuilder;

    protected function setUp()
    {
        $this->_addressBuilder = new \Magento\Customer\Service\V1\Dto\AddressBuilder(
            new \Magento\Customer\Service\V1\Dto\RegionBuilder()
        );
    }

    public function testMinimalAddress()
    {
        $this->_fillMinimumRequiredFields($this->_addressBuilder);
        $this->_assertMinimumRequiredFields($this->_addressBuilder->create());
    }

    public function testCopyAndModify()
    {
        /** @var \Magento\Customer\Service\V1\Dto\Address $origAddress */
        $origAddress = $this->getMockBuilder('\Magento\Customer\Service\V1\Dto\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockReturnValue($origAddress, array(
            'getFirstname' => $this->_expectedValues['firstname'],
            'getLastname' => $this->_expectedValues['lastname'],
            'getStreet' => $this->_expectedValues['street'],
            'getCity' => $this->_expectedValues['city'],
            'getCountryId' => $this->_expectedValues['country_id'],
            'getRegion' => new \Magento\Customer\Service\V1\Dto\Region(['region' => 'Texas', 'region_code' => '']),
            'getPostcode' => $this->_expectedValues['postcode'],
            'getTelephone' => $this->_expectedValues['telephone'],
        ));

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
        unset($expected['id']);
        unset($expected['default_billing']);
        unset($expected['default_shipping']);
        $this->assertEquals($expected, $this->_addressBuilder->create()->getAttributes());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
    }

    /**
     * @param AddressBuilder $address
     */
    private function _fillMinimumRequiredFields($addressBuilder)
    {
        $addressBuilder->setFirstname($this->_expectedValues['firstname']);
        $addressBuilder->setLastname($this->_expectedValues['lastname']);
        $addressBuilder->setStreet($this->_expectedValues['street']);
        $addressBuilder->setCity($this->_expectedValues['city']);
        $addressBuilder->setCountryId($this->_expectedValues['country_id']);
        $addressBuilder->setRegion(
            new \Magento\Customer\Service\V1\Dto\Region(['region' => $this->_expectedValues['region']['region'],
                'region_code' => ''])
        );
        $addressBuilder->setPostcode($this->_expectedValues['postcode']);
        $addressBuilder->setTelephone($this->_expectedValues['telephone']);
    }

    /**
     * @param Address $address
     */
    private function _fillAllFields($addressBuilder)
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
    private function _assertMinimumRequiredFields($address)
    {
        $this->assertEquals($this->_expectedValues['firstname'], $address->getFirstname());
        $this->assertEquals($this->_expectedValues['lastname'], $address->getLastname());
        $this->assertEquals($this->_expectedValues['street'][0], $address->getStreet()[0]);
        $this->assertEquals($this->_expectedValues['city'], $address->getCity());
        $this->assertEquals($this->_expectedValues['country_id'], $address->getCountryId());
        $this->assertEquals(
            new \Magento\Customer\Service\V1\Dto\Region(['region' => $this->_expectedValues['region']['region'],
                'region_code' => '']),
            $address->getRegion()
        );
        $this->assertEquals($this->_expectedValues['postcode'], $address->getPostcode());
        $this->assertEquals($this->_expectedValues['telephone'], $address->getTelephone());
    }
}
