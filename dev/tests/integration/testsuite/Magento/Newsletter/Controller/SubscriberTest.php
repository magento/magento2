<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberLoader;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test Subscriber
 */
class SubscriberTest extends AbstractController
{
    public function testNewAction()
    {
        $this->getRequest()->setMethod('POST');

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->isEmpty());
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testNewActionUnusedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue(['email' => 'not_used@example.com']);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionUsedEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue(['email' => 'customer@example.com']);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewActionOwnerEmail()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue(['email' => 'customer@example.com']);
        $this->login(1);

        $this->dispatch('newsletter/subscriber/new');

        $this->assertSessionMessages($this->equalTo(['Thank you for your subscription.']));
        $this->assertRedirect($this->anything());
    }

    /**
     * Check that Customer still subscribed for newsletters emails after registration.
     *
     * @magentoDbIsolation enabled
     */
    public function testCreatePosWithSubscribeEmailAction()
    {
        $this->markTestSkipped('Skip until failed. MAGETWO-96420');

        $config = Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
        $accountConfirmationRequired = $config->getValue(
            AccountConfirmation::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES
        );
        $config->setValue(AccountConfirmation::XML_PATH_IS_CONFIRM, 1, ScopeInterface::SCOPE_WEBSITES);

        $subscriber = Bootstrap::getObjectManager()->create(Subscriber::class);
        $customerEmail = 'subscribeemail@example.com';
        // Subscribe by email
        $subscriber->subscribe($customerEmail);
        $subscriber->loadByEmail($customerEmail);
        $subscriber->confirm($subscriber->getSubscriberConfirmCode());

        // Create customer
        $this->fillRequestWithAccountDataAndFormKey($customerEmail);
        $this->dispatch('customer/account/createPost');
        $this->dispatch('customer/account/confirm');

        $customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        /** @var  \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $customerRepository->get($customerEmail);
        $subscriberResource = Bootstrap::getObjectManager()
            ->create(SubscriberLoader::class);

        // check customer subscribed to newsletter
        $this->assertTrue($subscriberResource->loadByCustomerData($customer)['subscriber_status'] === "1");

        $config->setValue(
            AccountConfirmation::XML_PATH_IS_CONFIRM,
            $accountConfirmationRequired,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * Customer Data.
     *
     * @param string $email
     * @return void
     */
    private function fillRequestWithAccountDataAndFormKey($email)
    {
        Bootstrap::getObjectManager()->get(RequestInterface::class)
            ->setMethod('POST')
            ->setParam('firstname', 'firstname1')
            ->setParam('lastname', 'lastname1')
            ->setParam('company', '')
            ->setParam('email', $email)
            ->setParam('password', '_Password1')
            ->setParam('password_confirmation', '_Password1')
            ->setParam('telephone', '5123334444')
            ->setParam('street', ['1234 fake street', ''])
            ->setParam('city', 'Austin')
            ->setParam('region_id', 57)
            ->setParam('region', '')
            ->setParam('postcode', '78701')
            ->setParam('country_id', 'US')
            ->setParam('default_billing', '1')
            ->setParam('default_shipping', '1')
            ->setParam('is_subscribed', '0')
            ->setPostValue('create_address', true)
            ->setParam('form_key', Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey());
    }

    /**
     * Login the user
     *
     * @param string $customerId Customer to mark as logged in for the session
     * @return void
     */
    protected function login($customerId)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\Session::class);
        $session->loginById($customerId);
    }
}
