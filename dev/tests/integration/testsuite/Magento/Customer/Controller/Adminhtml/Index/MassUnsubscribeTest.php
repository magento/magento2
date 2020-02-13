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
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
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

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CollectionFactory */
    private $subscriberCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->subscriberCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/three_subscribers.php
     *
     * @return void
     */
    public function testMassUnsubscribeAction(): void
    {
        $params = [
            'selected' => [1, 2, 3],
            'namespace' => 'customer_listing',
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massUnsubscribe');
        $this->assertRedirect($this->stringContains('backend/customer/index/index'));
        $this->assertSessionMessages(
            $this->equalTo([(string)__('A total of 3 record(s) were updated.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $emails = ['customer@search.example.com', 'customer2@search.example.com', 'customer3@search.example.com'];
        $collection = $this->subscriberCollectionFactory->create()->addFieldToFilter('subscriber_email', $emails)
            ->addFieldToSelect('subscriber_status');
        $this->assertCount(3, $collection);
        foreach ($collection as $subscriber) {
            $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $subscriber->getData('subscriber_status'));
        }
    }

    /**
     * @return void
     */
    public function testMassSubscriberActionNoSelection(): void
    {
        $params = [
            'namespace' => 'customer_listing',
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/massUnsubscribe');
        $this->assertRedirect($this->stringContains('backend/customer/index/index'));
        $this->assertSessionMessages(
            $this->equalTo([(string)__('An item needs to be selected. Select and try again.')]),
            MessageInterface::TYPE_ERROR
        );
    }
}
