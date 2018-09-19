<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\Message\MessageInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * @magentoAppArea adminhtml
 */
class MassSubscribeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $baseControllerUrl = 'http://localhost/index.php/backend/customer/index/index';

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->getMessages(true);
    }

    /**
     * Tests subscriber status of customers.
     *
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     */
    public function testMassSubscriberAction()
    {
        /** @var SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get(SubscriberFactory::class);
        $customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);

        $this->assertNull(
            $subscriberFactory->create()
                ->loadByEmail('customer1@example.com')
                ->getSubscriberStatus()
        );
        $this->assertNull(
            $subscriberFactory->create()
                ->loadByEmail('customer2@example.com')
                ->getSubscriberStatus()
        );

        try {
            /** @var CustomerInterface $customer1 */
            $customer1 = $customerRepository->get('customer1@example.com');
            /** @var CustomerInterface $customer2 */
            $customer2 = $customerRepository->get('customer2@example.com');
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $params = [
            'selected' => [
                $customer1->getId(),
                $customer2->getId(),
            ],
            'namespace' => 'customer_listing',
            'form_key' => $formKey->getFormKey()
        ];
        $this->getRequest()
            ->setParams($params)
            ->setMethod('POST');

        $this->dispatch('backend/customer/index/massSubscribe');

        // Assertions
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        $this->assertSessionMessages(
            self::equalTo(['A total of 2 record(s) were updated.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()
                ->loadByEmail('customer1@example.com')
                ->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()
                ->loadByEmail('customer2@example.com')
                ->getSubscriberStatus()
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testMassSubscriberActionNoSelection()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $params = [
            'namespace' => 'customer_listing',
            'form_key' => $formKey->getFormKey()
        ];

        $this->getRequest()
            ->setParams($params)
            ->setMethod('POST');
        $this->dispatch('backend/customer/index/massSubscribe');

        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        $this->assertSessionMessages(
            self::equalTo(['Please select item(s).']),
            MessageInterface::TYPE_ERROR
        );
    }
}
