<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class MassAssignGroupTest extends AbstractBackendController
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $baseControllerUrl = 'http://localhost/index.php/backend/customer/index/index';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getMessages(true);
    }

    /**
     * Tests os update a single customer record.
     *
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     */
    public function testMassAssignGroupAction()
    {
        $customerEmail = 'customer1@example.com';
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->get($customerEmail);
            $this->assertEquals(1, $customer->getGroupId());

            /** @var \Magento\Framework\Data\Form\FormKey $formKey */
            $formKey = $this->_objectManager->get(
                \Magento\Framework\Data\Form\FormKey::class
            );

            $params = [
                'group' => 0,
                'namespace' => 'customer_listing',
                'selected' => [$customer->getId()],
                'form_key' => $formKey->getFormKey()
            ];

            $this->getRequest()
                ->setParams($params)
                ->setMethod('POST');
            $this->dispatch('backend/customer/index/massAssignGroup');
            $this->assertSessionMessages(
                self::equalTo(['A total of 1 record(s) were updated.']),
                MessageInterface::TYPE_SUCCESS
            );
            $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));

            $customer = $this->customerRepository->get($customerEmail);
            $this->assertEquals(0, $customer->getGroupId());
        } catch (LocalizedException $e) {
            self::fail($e->getMessage());
        }
    }

    /**
     * Tests os update a multiple customer records.
     *
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     */
    public function testLargeGroupMassAssignGroupAction()
    {
        $ids = [];
        for ($i = 1; $i <= 5; $i++) {
            /** @var CustomerInterface $customer */
            try {
                $customer = $this->customerRepository->get('customer'.$i.'@example.com');
                $this->assertEquals(1, $customer->getGroupId());
                $ids[] = $customer->getId();
            } catch (\Exception $e) {
                self::fail($e->getMessage());
            }
        }

        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $params = [
            'group' => 0,
            'namespace' => 'customer_listing',
            'selected' => $ids,
            'form_key' => $formKey->getFormKey()
        ];

        $this->getRequest()
            ->setParams($params)
            ->setMethod('POST');

        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            self::equalTo(['A total of 5 record(s) were updated.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        for ($i = 1; $i < 5; $i++) {
            try {
                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->get('customer'.$i.'@example.com');
                $this->assertEquals(0, $customer->getGroupId());
            } catch (\Exception $e) {
                self::fail($e->getMessage());
            }
        }
    }

    /**
     * Valid group Id but no customer Ids specified
     *
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionNoCustomerIds()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $params = [
            'group' => 0,
            'namespace' => 'customer_listing',
            'form_key' => $formKey->getFormKey()
        ];
        $this->getRequest()
            ->setParams($params)
            ->setMethod('POST');
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['Please select item(s).']),
            MessageInterface::TYPE_ERROR
        );
    }
}
