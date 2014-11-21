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

namespace Magento\Customer\Model\Resource;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\AddressConverter;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for service layer \Magento\Customer\Service\V1\CustomerAddressService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class AddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Api\AddressRepositoryInterface */
    private $_service;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Data\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    private $_addressBuilder;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_service = $this->_objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Model\Data\AddressBuilder');

        $builder = $this->_objectManager->create('Magento\Customer\Model\Data\RegionBuilder');
        $region = $builder->setRegionCode('AL')
            ->setRegion('Alabama')
            ->setRegionId(1)
            ->create();

        $this->_addressBuilder
            ->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setPostcode('75477')
            ->setRegion($region)
            ->setStreet(array('Green str, 67'))
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setCompany('CompanyName');
        $address = $this->_addressBuilder->create();

        $this->_addressBuilder
            ->setId(2)
            ->setCustomerId(2)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setPostcode('47676')
            ->setRegion($region)
            ->setStreet(array('Black str, 48'))
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');

        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = array($address, $address2);
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Model\AddressRegistry $addressRegistry */
        $customerRegistry = $objectManager->get('Magento\Customer\Model\CustomerRegistry');
        $customerRegistry->remove(1);
    }


    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testSaveNewAddress()
    {
        $this->markTestSkipped('Should be fixed in scope of MAGETWO-29651');
        $this->_addressBuilder->populate($this->_expectedAddresses[1])->setId(null);
        $proposedAddress = $this->_addressBuilder->create();
        $customerId = 1;
        $createdAddress = $this->_service->save($proposedAddress);
        $addresses = $this->_service->get($customerId);
        $this->assertEquals($this->_expectedAddresses[0], $addresses[0]);
        $expectedNewAddressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[1]);
        $expectedNewAddressBuilder->setId($addresses[1]->getId());
        $expectedNewAddress = $expectedNewAddressBuilder->create();
        $this->assertEquals(
            AddressConverter::toFlatArray($expectedNewAddress),
            AddressConverter::toFlatArray($addresses[1])
        );
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoAppIsolation enabled
     */
    public function testGetAddress()
    {
        $this->markTestSkipped('Should be fixed in scope of MAGETWO-29651');
        //$address = $this->_service->get(1);
        //$addresses = $this->_service->get($customerId);
        //$this->assertEquals($this->_expectedAddresses[0], $addresses[0]);
        //$expectedNewAddressBuilder = $this->_addressBuilder->populate($this->_expectedAddresses[1]);
        //$expectedNewAddressBuilder->setId($addresses[1]->getId());
        //$expectedNewAddress = $expectedNewAddressBuilder->create();
        //$this->assertEquals(
        //    AddressConverter::toFlatArray($expectedNewAddress),
        //    AddressConverter::toFlatArray($addresses[1])
        //);
    }

    /**
     * @param \Magento\Framework\Api\Filter[] $filters
     * @param \Magento\Framework\Api\Filter[] $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchAddressDataProvider
     *
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     */
    public function testSearchAddresses($filters, $filterGroup, $expectedResult)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->_service->getList($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        /** @var $item Data\CustomerDetails */
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals(
                $expectedResult[$item->getId()]['city'],
                $item->getCity()
            );
            $this->assertEquals(
                $expectedResult[$item->getId()]['postcode'],
                $item->getPostcode()
            );
            $this->assertEquals(
                $expectedResult[$item->getId()]['firstname'],
                $item->getFirstname()
            );
            unset($expectedResult[$item->getId()]);
        }
    }

    public function searchAddressDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
        return [
            'Address with postcode 75477' => [
                [$builder->setField('postcode')->setValue('75477')->create()],
                null,
                [1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John']]
            ],
            'Address with city CityM' => [
                [$builder->setField('city')->setValue('CityM')->create()],
                null,
                [1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John']]
            ],
            'Addresses with firstname John' => [
                [$builder->setField('firstname')->setValue('John')->create()],
                null,
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ]
            ],
            'Addresses with postcode of either 75477 or 47676' => [
                [],
                [
                    $builder->setField('postcode')->setValue('75477')->create(),
                    $builder->setField('postcode')->setValue('47676')->create()
                ],
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ]
            ],
            'Addresses with postcode greater than 0' => [
                [$builder->setField('postcode')->setValue('0')->setConditionType('gt')->create()],
                null,
                [
                    1 => ['city' => 'CityM', 'postcode' => 75477, 'firstname' => 'John'],
                    2 => ['city' => 'CityX', 'postcode' => 47676, 'firstname' => 'John']
                ]
            ]
        ];
    }
}
