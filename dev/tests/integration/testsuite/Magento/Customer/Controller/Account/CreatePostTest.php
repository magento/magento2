<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class CreatePostTest extends \Magento\TestFramework\TestCase\AbstractController
{
    const EXPECTED_DOB = '1991-12-31';
    const EXPECTED_DATE = '2017-12-25 00:00:00';

    /**
     * @var CreatePost
     */
    private $model;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(CreatePost::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/date_attribute.php
     * @magentoDataFixture Magento/Customer/_files/customer_date_attribute.php
     */
    public function testCustomerSaveWithDateAttributes()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
        $repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer1 = $repository->get('john.doe1@ex.com', 1);
        $customerDob = $customer1->getDob();
        $customerDate = $customer1->getCustomAttribute('date')->getValue();
        $this->assertEquals(self::EXPECTED_DOB, $customerDob);
        $this->assertEquals(self::EXPECTED_DATE, $customerDate);

        $customer2 = $repository->get('john.doe2@ex.com', 1);
        $customerDob = $customer2->getDob();
        $customerDate = $customer2->getCustomAttribute('date')->getValue();
        $this->assertEquals(self::EXPECTED_DOB, $customerDob);
        $this->assertEquals(self::EXPECTED_DATE, $customerDate);

        $customer3 = $repository->get('john.doe3@ex.com', 1);
        $customerDob = $customer3->getDob();
        $customerDate = $customer3->getCustomAttribute('date')->getValue();
        $this->assertEquals(self::EXPECTED_DOB, $customerDob);
        $this->assertEquals(self::EXPECTED_DATE, $customerDate);
    }
}
