<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model\ResourceModel;

use Magento\TestFramework\Helper\Bootstrap;

class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = Bootstrap::getObjectManager()
            ->create(\Magento\Newsletter\Model\ResourceModel\Subscriber::class);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testLoadByCustomerDataWithCustomerId()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById(1);
        $result = $this->_resourceModel->loadByCustomerData($customerData);

        $this->assertEquals(1, $result['customer_id']);
        $this->assertEquals('customer@example.com', $result['subscriber_email']);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testLoadByCustomerDataWithoutCustomerId()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById(2);
        $result = $this->_resourceModel->loadByCustomerData($customerData);

        $this->assertEquals(0, $result['customer_id']);
        $this->assertEquals('customer_two@example.com', $result['subscriber_email']);
    }
}
