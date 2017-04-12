<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Newsletter\Model\Subscriber;
use Magento\TestFramework\Helper\Bootstrap;

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
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testMassSubscriberAction()
    {
        // Pre-condition
        /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
        $subscriberFactory = Bootstrap::getObjectManager()->get(\Magento\Newsletter\Model\SubscriberFactory::class);
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus());
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus());
        // Setup
        $this->getRequest()->setPostValue('selected', [1, 2])->setPostValue('namespace', 'customer_listing');

        // Test
        $this->dispatch('backend/customer/index/massSubscribe');

        // Assertions
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus()
        );
        $this->assertEquals(
            Subscriber::STATUS_SUBSCRIBED,
            $subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus()
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMassSubscriberActionNoSelection()
    {
        $this->getRequest()->setPostValue('namespace', 'customer_listing');
        $this->dispatch('backend/customer/index/massSubscribe');

        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        $this->assertSessionMessages(
            $this->equalTo(['Please select item(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
