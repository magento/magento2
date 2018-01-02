<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Customer/_files/customer_date_attribute.php
 */
class CreatePostTest extends \Magento\TestFramework\TestCase\AbstractController
{
    const DOB = '1991-12-31';
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
     * @param string $dob
     * @param string $date
     * @param string $expectedDob
     * @param string $expectedDate
     * @param string $locale
     * @param string $email
     * @dataProvider getDate
     * @magentoDbIsolation enabled
     */
    public function testCustomerSaveWithDateAttributes($dob, $date, $expectedDob, $expectedDate, $locale, $email)
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Locale\ResolverInterface')->setLocale($locale);

        /** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
        $repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $objectManager->create('Magento\Customer\Api\Data\CustomerInterface');

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer->setWebsiteId(1)
            ->setEmail($email)
            ->setGroupId(1)
            ->setStoreId(1)
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1)
            ->setTaxvat('12')
            ->setGender(0)
            ->setDob($dob)
            ->setCustomAttribute('date', $date);
        $repository->save($customer, 'password');

        $customer = $repository->get($email);
        $customerDob = $customer->getDob();
        $customerDate = $customer->getCustomAttribute('date')->getValue();
        $this->assertEquals($expectedDob, $customerDob);
        $this->assertEquals($expectedDate, $customerDate);
    }

    public function getDate()
    {
        return [
            [self::DOB, '12/25/2017', self::EXPECTED_DOB, SELF::EXPECTED_DATE, 'en_US', 'customer1@example.com'],
            [self::DOB, '25/12/2017', self::EXPECTED_DOB, SELF::EXPECTED_DATE, 'fr_FR', 'customer2@example.com'],
            [self::DOB, '25/12/2017', self::EXPECTED_DOB, SELF::EXPECTED_DATE, 'ar_KW', 'customer3@example.com'],
        ];
    }
}
