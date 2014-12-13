<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Downloadable\Test\Page\DownloadableCustomerProducts;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer.
 * 2. Create downloadable product.
 * 3. Place order.
 * 4. Create invoice.
 * 5. Go to customer account > My Downloads and click download link.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Reports > Products > Downloads.
 * 3. Perform all assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28823
 */
class DownloadProductsReportEntityTest extends Injectable
{
    /**
     * Browser Interface.
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Customer Account index page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccount;

    /**
     * Downloadable Customer Products page.
     *
     * @var DownloadableCustomerProducts
     */
    protected $customerProducts;

    /**
     * Inject pages.
     *
     * @param CustomerAccountIndex $customerAccount
     * @param DownloadableCustomerProducts $customerProducts
     * @param Browser $browser
     * @return void
     */
    public function __inject(
        CustomerAccountIndex $customerAccount,
        DownloadableCustomerProducts $customerProducts,
        Browser $browser
    ) {
        $this->customerAccount = $customerAccount;
        $this->customerProducts = $customerProducts;
        $this->browser = $browser;
    }

    /**
     * Order downloadable product.
     *
     * @param OrderInjectable $order
     * @param string $downloads
     * @return void
     */
    public function test(OrderInjectable $order, $downloads)
    {
        $this->markTestIncomplete('MAGETWO-30346');
        // Preconditions
        $order->persist();
        $invoice = $this->objectManager->create('Magento\Sales\Test\TestStep\CreateInvoiceStep', ['order' => $order]);
        $invoice->run();
        $this->openDownloadableLink($order, (int)$downloads);
    }

    /**
     * Open Downloadable Link.
     *
     * @param OrderInjectable $order
     * @param int $downloads
     * @return void
     */
    protected function openDownloadableLink(OrderInjectable $order, $downloads)
    {
        $customerLogin = $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()]
        );
        $customerLogin->run();
        $this->customerAccount->getAccountMenuBlock()->openMenuItem('My Downloadable Products');
        foreach ($order->getEntityId()['products'] as $product) {
            foreach ($product->getDownloadableLinks()['downloadable']['link'] as $link) {
                for ($i = 0; $i < $downloads; $i++) {
                    $this->customerProducts->getMainBlock()->openLink($link['title']);
                    $this->browser->selectWindow();
                    $this->browser->closeWindow();
                    $this->browser->selectWindow();
                }
            }
        }
    }
}
