<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model\ResourceModel\Problem;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Problem\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = Bootstrap::getObjectManager()
            ->create(\Magento\Newsletter\Model\ResourceModel\Problem\Collection::class);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/problems.php
     */
    public function testAddCustomersData()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = Bootstrap::getObjectManager()
            ->create(\Magento\Newsletter\Model\Subscriber::class)->loadByEmail($customer->getEmail());
        /** @var \Magento\Newsletter\Model\Problem $problem */
        $problem = Bootstrap::getObjectManager()
            ->create(\Magento\Newsletter\Model\Problem::class)->addSubscriberData($subscriber);

        $item = $this->_collection->addSubscriberInfo()->load()->getFirstItem();

        $this->assertEquals($problem->getProblemErrorCode(), $item->getErrorCode());
        $this->assertEquals($problem->getProblemErrorText(), $item->getErrorText());
        $this->assertEquals($problem->getSubscriberId(), $item->getSubscriberId());
        $this->assertEquals($customer->getEmail(), $item->getSubscriberEmail());
        $this->assertEquals($customer->getFirstname(), $item->getCustomerFirstName());
        $this->assertEquals($customer->getLastname(), $item->getCustomerLastName());
        $this->assertContains($customer->getFirstname(), $item->getCustomerName());
    }
}
