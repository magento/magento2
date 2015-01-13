<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CalculationTest
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_address.php
 */
class CalculationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    const FIXTURE_CUSTOMER_ID = 1;

    const FIXTURE_ADDRESS_ID = 1;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_model;

    protected function setUp()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create('Magento\Tax\Model\Calculation');
        $this->customerRepository = $this->_objectManager->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->addressRepository = $this->_objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');
        $this->groupRepository = $this->_objectManager->create('Magento\Customer\Api\GroupRepositoryInterface');
    }

    public function testDefaultCustomerTaxClass()
    {
        $defaultCustomerTaxClass = 3;
        $this->assertEquals($defaultCustomerTaxClass, $this->_model->getDefaultCustomerTaxClass(null));
    }

    public function testGetDefaultRateRequest()
    {
        $customerDataSet = $this->customerRepository->getById(self::FIXTURE_CUSTOMER_ID);
        $address = $this->addressRepository->getById(self::FIXTURE_ADDRESS_ID);

        $rateRequest = $this->_model->getRateRequest(null, null, null, null, $customerDataSet->getId());

        $this->assertNotNull($rateRequest);
        $this->assertEquals($address->getCountryId(), $rateRequest->getCountryId());
        $this->assertEquals($address->getRegion()->getRegionId(), $rateRequest->getRegionId());
        $this->assertEquals($address->getPostcode(), $rateRequest->getPostcode());

        $customerTaxClassId = $this->groupRepository->getById($customerDataSet->getGroupId())->getTaxClassId();
        $this->assertEquals($customerTaxClassId, $rateRequest->getCustomerClassId());
    }
}
