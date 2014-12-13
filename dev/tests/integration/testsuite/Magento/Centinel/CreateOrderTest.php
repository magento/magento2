<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Centinel;

/**
 * @magentoAppArea adminhtml
 */
class CreateOrderTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Check if 3d-secure functionality html-code exists on page when for selected method it is enabled
     *
     * @magentoConfigFixture default_store payment/authorizenet/centinel 1
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testIndexAction()
    {
        /** @var $order \Magento\Sales\Model\AdminOrder\Create */
        $order = $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create');
        $quote = $order->addProducts([1 => ['qty' => 1]])->getQuote();

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        $quote->setCustomer($customer);

        $defaultStore = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $defaultStoreId = $defaultStore->getStore('default')->getId();
        $quote->setStoreId($defaultStoreId);

        $quote->getPayment()->addData([
            'cc_owner' => 'Test User',
            'cc_type' => 'visa',
            'cc_number' => '400000000000002',
            'cc_exp_month' => '1',
            'cc_exp_year' => '2016',
            'cc_cid' => '123',
            'method' => 'authorizenet'
        ]);

        $this->dispatch('backend/sales/order_create/index');

        $this->assertContains('<div class="centinel">', $this->getResponse()->getBody());
    }
}
