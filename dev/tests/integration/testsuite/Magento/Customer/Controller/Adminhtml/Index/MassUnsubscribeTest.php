<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Backend\Model\Session;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class MassUnsubscribeTest
 * @package Magento\Customer\Controller\Adminhtml\Index
 */
class MassUnsubscribeTest extends AbstractBackendController
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
        Bootstrap::getObjectManager()->get(Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(Session::class)->getMessages(true);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers_with_reindex.php
     */
    public function testMassUnsubscribAction()
    {
        $subscriberFactory = Bootstrap::getObjectManager()->get(SubscriberFactory::class);
        $formKey = $this->_objectManager->get(FormKey::class);
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(1)->getSubscriberStatus());
        $this->assertNull($subscriberFactory->create()->loadByCustomerId(2)->getSubscriberStatus());

        $this->getRequest()->setParams(
            [
                'selected' => [1, 2],
                'namespace' => 'customer_listing',
                'form_key' => $formKey->getFormKey()
            ]
        )
            ->setMethod('POST');
        $this->dispatch('backend/customer/index/massUnsubscribe');

        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        $this->assertSessionMessages(
            $this->equalTo(['A total of 2 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
