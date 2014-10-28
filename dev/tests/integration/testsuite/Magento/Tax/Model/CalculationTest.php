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
namespace Magento\Tax\Model;

use Magento\Customer\Service\V1\CustomerAddressService;
use Magento\Customer\Service\V1\CustomerGroupService;
use Magento\Customer\Service\V1\CustomerAccountService;

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
     * @var CustomerAccountService
     */
    protected $_customerAccountService;

    /**
     * @var CustomerAddressService
     */
    protected $_addressService;

    /**
     * @var CustomerGroupService
     */
    protected $_groupService;

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
        $this->_customerAccountService = $this->_objectManager->create(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $this->_addressService = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAddressService');
        $this->_groupService = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerGroupService');
    }

    public function testDefaultCustomerTaxClass()
    {
        $defaultCustomerTaxClass = 3;
        $this->assertEquals($defaultCustomerTaxClass, $this->_model->getDefaultCustomerTaxClass(null));
    }

    public function testGetDefaultRateRequest()
    {
        $customerDataSet = $this->_customerAccountService->getCustomer(self::FIXTURE_CUSTOMER_ID);
        $address = $this->_addressService->getAddress(self::FIXTURE_ADDRESS_ID);

        $rateRequest = $this->_model->getRateRequest(null, null, null, null, $customerDataSet->getId());

        $this->assertNotNull($rateRequest);
        $this->assertEquals($address->getCountryId(), $rateRequest->getCountryId());
        $this->assertEquals($address->getRegion()->getRegionId(), $rateRequest->getRegionId());
        $this->assertEquals($address->getPostcode(), $rateRequest->getPostcode());

        $customerTaxClassId = $this->_groupService->getGroup($customerDataSet->getGroupId())->getTaxClassId();
        $this->assertEquals($customerTaxClassId, $rateRequest->getCustomerClassId());
    }
}
