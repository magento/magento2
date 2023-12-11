<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Customer\Api\Data\CustomerInterface;
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

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get(Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(Session::class)->getMessages(true);
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
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->get($customerEmail);
        $this->assertEquals(1, $customer->getGroupId());

        $params = [
            'group' => 0,
            'namespace' => 'customer_listing',
            'selected' => [$customer->getId()]
        ];

        $this->getRequest()->setParams($params)
            ->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            self::equalTo(['A total of 1 record(s) were updated.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));

        $customer = $this->customerRepository->get($customerEmail);
        $this->assertEquals(0, $customer->getGroupId());
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
            $customer = $this->customerRepository->get('customer' . $i . '@example.com');
            $this->assertEquals(1, $customer->getGroupId());
            $ids[] = $customer->getId();
        }

        $params = [
            'group' => 0,
            'namespace' => 'customer_listing',
            'selected' => $ids,
        ];

        $this->getRequest()->setParams($params)
            ->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            self::equalTo(['A total of 5 record(s) were updated.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        for ($i = 1; $i < 5; $i++) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->get('customer' . $i . '@example.com');
            $this->assertEquals(0, $customer->getGroupId());
        }
    }

    /**
     * Valid group Id but no customer Ids specified
     *
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionNoCustomerIds()
    {
        $params = ['group'=> 0,'namespace'=> 'customer_listing',
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['An item needs to be selected. Select and try again.']),
            MessageInterface::TYPE_ERROR
        );
    }
}
