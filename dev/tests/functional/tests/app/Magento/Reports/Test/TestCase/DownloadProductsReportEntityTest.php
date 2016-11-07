<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Downloadable\Test\Page\DownloadableCustomerProducts;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
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
 * @group Reports
 * @ZephyrId MAGETWO-28823
 */
class DownloadProductsReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Browser Interface.
     *
     * @var BrowserInterface
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
     * @param BrowserInterface $browser
     * @return void
     */
    public function __inject(
        CustomerAccountIndex $customerAccount,
        DownloadableCustomerProducts $customerProducts,
        BrowserInterface $browser
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
        // Preconditions
        $order->persist();
        $invoice = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            ['order' => $order]
        );
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
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()]
        );
        $customerLogin->run();
        $this->customerAccount->getAccountMenuBlock()->openMenuItem('My Downloadable Products');
        $downloadableProductsUrl = $this->browser->getUrl();
        foreach ($order->getEntityId()['products'] as $product) {
            foreach ($product->getDownloadableLinks()['downloadable']['link'] as $link) {
                for ($i = 0; $i < $downloads; $i++) {
                    $this->browser->open($this->customerProducts->getMainBlock()->getLinkUrl($link['title']));
                    $this->browser->open($downloadableProductsUrl);
                }
            }
        }
    }
}
