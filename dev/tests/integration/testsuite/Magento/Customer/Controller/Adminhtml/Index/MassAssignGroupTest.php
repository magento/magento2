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
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testMassAssignGroupAction()
    {
        $customer = $this->customerRepository->getById(1);

        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);

        $this->assertEquals(1, $customer->getGroupId());

        $this->getRequest()
            ->setParams(
                [
                    'group' => 0,
                    'namespace' => 'customer_listing',
                    'selected' => [1],
                    'form_key' => $formKey->getFormKey()
                ]
            )
            ->setMethod('POST');
        
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
     * Valid group Id but no customer Ids specified
     * @magentoDbIsolation enabled
     */
    public function testMassAssignGroupActionNoCustomerIds()
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);

        $this->getRequest()
            ->setParams(
                [
                    'group' => 0,
                    'namespace' => 'customer_listing',
                    'form_key' => $formKey->getFormKey()
                ]
            )
            ->setMethod('POST');

        $this->dispatch('backend/customer/index/massAssignGroup');
        $this->assertSessionMessages(
            $this->equalTo(['Please select item(s).']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
