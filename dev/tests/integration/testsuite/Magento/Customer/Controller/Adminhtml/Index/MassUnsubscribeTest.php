<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test mass subscribe action on customers grid.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassUnsubscribeTest extends AbstractBackendController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SubscriberFactory */
    private $subscriberFactory;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->subscriberFactory = $this->objectManager->get(SubscriberFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/two_subscribers.php
     *
     * @return void
     */
    public function testMassUnsubscribeAction(): void
    {
        $firstCustomer = $this->customerRepository->get('customer@example.com');
        $secondCustomer = $this->customerRepository->get('customer_two@example.com');
        $params = [
            'selected' => [
                $firstCustomer->getId(),
                $secondCustomer->getId(),
            ],
            'namespace' => 'customer_listing',
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massUnsubscribe');
        $this->assertRedirect($this->stringContains('backend/customer/index/index'));
        $this->assertSessionMessages(
            self::equalTo(['A total of 2 record(s) were updated.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $this->subscriberFactory->create()
                ->loadByEmail('customer@example.com')->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_UNSUBSCRIBED,
            $this->subscriberFactory->create()
                ->loadByEmail('customer_two@example.com')->getSubscriberStatus()
        );
    }

    /**
     * @return void
     */
    public function testMassSubscriberActionNoSelection(): void
    {
        $params = [
            'namespace' => 'customer_listing'
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massUnsubscribe');
        $this->assertRedirect($this->stringContains('backend/customer/index/index'));
        $this->assertSessionMessages(
            self::equalTo(['An item needs to be selected. Select and try again.']),
            MessageInterface::TYPE_ERROR
        );
    }
}
