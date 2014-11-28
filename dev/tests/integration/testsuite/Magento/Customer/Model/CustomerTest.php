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

namespace Magento\Customer\Model;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

    protected function setUp()
    {
        $this->customerModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        );
        $this->customerBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerDataBuilder'
        );
    }

    public function testUpdateDataSetDataOnEmptyModel()
    {
        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $customerData = $this->customerBuilder
            ->setId(1)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setDefaultBilling(1)
            ->create();
        $customerData = $this->customerModel->updateData($customerData)->getDataModel();

        $this->assertEquals(1, $customerData->getId());
        $this->assertEquals('John', $customerData->getFirstname());
        $this->assertEquals('Doe', $customerData->getLastname());
        $this->assertEquals(1, $customerData->getDefaultBilling());
    }

    public function testUpdateDataOverrideExistingData()
    {
        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $customerData = $this->customerBuilder
            ->setId(2)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setDefaultBilling(1)
            ->create();
        $this->customerModel->updateData($customerData);

        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $updatedCustomerData = $this->customerBuilder
            ->setId(3)
            ->setFirstname('Jane')
            ->setLastname('Smith')
            ->setDefaultBilling(0)
            ->create();
        $updatedCustomerData = $this->customerModel->updateData($updatedCustomerData)->getDataModel();

        $this->assertEquals(3, $updatedCustomerData->getId());
        $this->assertEquals('Jane', $updatedCustomerData->getFirstname());
        $this->assertEquals('Smith', $updatedCustomerData->getLastname());
        $this->assertEquals(0, $updatedCustomerData->getDefaultBilling());
    }
}
