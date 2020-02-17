<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Manage;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Newsletter\Model\Plugin\CustomerPlugin;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class checks customer subscription
 *
 * @magentoDbIsolation enabled
 */
class SaveTest extends AbstractController
{
    /** @var Session */
    protected $customerSession;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var FormKey */
    private $formKey;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
        $this->customerRegistry = $this->_objectManager->get(CustomerRegistry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/new_customer.php
     *
     * @dataProvider subscriptionDataProvider
     *
     * @param string $isSubscribed
     * @param string $expectedMessage
     * @return void
     */
    public function testSaveAction(string $isSubscribed, string $expectedMessage): void
    {
        $this->loginCustomer('new_customer@example.com');
        $this->getRequest()->setParam('form_key', $this->formKey->getFormKey())
            ->setParam('is_subscribed', $isSubscribed);
        $this->_objectManager->removeSharedInstance(CustomerPlugin::class);
        $this->dispatch('newsletter/manage/save');
        $this->assertSuccessSubscription($expectedMessage);
    }

    /**
     * @return array
     */
    public function subscriptionDataProvider(): array
    {
        return [
            'subscribe_customer' => [
                'is_subscribed' => 1,
                'expected_message' => 'We have saved your subscription.',
            ],
            'unsubscribe_customer' => [
                'is_subscribed' => 0,
                'expected_message' => 'We have updated your subscription.',
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoDataFixture Magento/Customer/_files/new_customer.php
     *
     * @return void
     */
    public function testSubscribeWithEnabledConfirmation(): void
    {
        $this->loginCustomer('new_customer@example.com');
        $this->getRequest()->setParam('form_key', $this->formKey->getFormKey())->setParam('is_subscribed', '1');
        $this->dispatch('newsletter/manage/save');
        $this->assertSuccessSubscription('A confirmation request has been sent.');
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/customer_with_subscription.php
     *
     * @return void
     */
    public function testUnsubscribeSubscribedCustomer(): void
    {
        $this->loginCustomer('new_customer@example.com');
        $this->getRequest()->setParam('form_key', $this->formKey->getFormKey())->setParam('is_subscribed', '0');
        $this->_objectManager->removeSharedInstance(CustomerPlugin::class);
        $this->dispatch('newsletter/manage/save');
        $this->assertSuccessSubscription('We have removed your newsletter subscription.');
    }

    /**
     * Login customer by email
     *
     * @param string $email
     * @return void
     */
    private function loginCustomer(string $email): void
    {
        $customer = $this->customerRepository->get($email);
        $this->customerSession->loginById($customer->getId());
    }

    /**
     * Assert that action was successfully done
     *
     * @param string $expectedMessage
     * @return void
     */
    private function assertSuccessSubscription(string $expectedMessage): void
    {
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSessionMessages($this->equalTo([(string)__($expectedMessage)]), MessageInterface::TYPE_SUCCESS);
    }
}
