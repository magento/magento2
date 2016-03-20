<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class MassDeleteTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->getMessages(true);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMassDeleteAction()
    {
        $this->getRequest()->setPostValue('selected', [1])->setPostValue('namespace', 'customer_listing');
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 1 record(s) were deleted.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
    }

    /**
     * Valid group Id but no customer Ids specified
     * @magentoDbIsolation enabled
     */
    public function testMassDeleteActionNoCustomerIds()
    {
        $this->getRequest()->setPostValue('namespace', 'customer_listing');
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $this->equalTo(['Please select item(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
