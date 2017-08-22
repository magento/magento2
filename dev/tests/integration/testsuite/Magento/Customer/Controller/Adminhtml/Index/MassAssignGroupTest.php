<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * @magentoAppArea adminhtml
 */
class MassAssignGroupTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
    }

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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMassAssignGroupAction()
    {
        $customer = $this->customerRepository->getById(1);
        $this->assertEquals(1, $customer->getGroupId());

        $this->getRequest()
            ->setParam('group', 0)
            ->setPostValue('namespace', 'customer_listing')
            ->setPostValue('selected', [1]);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 1 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));

        $customer = $this->customerRepository->getById(1);
        $this->assertEquals(0, $customer->getGroupId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/twenty_one_customers.php
     */
    public function testLargeGroupMassAssignGroupAction()
    {

        for($i = 1; $i < 22; $i++){
            $customer = $this->customerRepository->getById($i);
            $this->assertEquals(1, $customer->getGroupId());
        }

        $this->getRequest()
            ->setParam('group', 0)
            ->setPostValue('namespace', 'customer_listing')
            ->setPostValue('selected', [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21]);
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['A total of 21 record(s) were updated.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
        for($i = 1; $i < 22; $i++){
            $customer = $this->customerRepository->getById($i);
            $this->assertEquals(0, $customer->getGroupId());
        }
    }

    /**
     * Valid group Id but no customer Ids specified
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionNoCustomerIds()
    {
        $this->getRequest()->setParam('group', 0)->setPostValue('namespace', 'customer_listing');
        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['Please select item(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
