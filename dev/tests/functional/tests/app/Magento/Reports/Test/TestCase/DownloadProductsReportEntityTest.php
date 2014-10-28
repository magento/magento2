<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Downloadable\Test\Page\DownloadableCustomerProducts;
use Mtf\TestCase\Injectable;
use Mtf\Client\Browser;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Test Creation for DownloadProductsReportEntity
 *
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
     * Browser Interface
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Customer Account index page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccount;

    /**
     * Downloadable Customer Products page
     *
     * @var DownloadableCustomerProducts
     */
    protected $customerProducts;

    /**
     * Inject pages
     *
     * @param CustomerAccountIndex $customerAccount
     * @param DownloadableCustomerProducts $customerProducts
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
     * Order downloadable product
     *
     * @param OrderInjectable $order
     * @param string $downloads
     * @return void
     */
    public function test(OrderInjectable $order, $downloads)
    {
        // Preconditions
        $order->persist();
        $invoice = $this->objectManager->create('Magento\Sales\Test\TestStep\CreateInvoiceStep', ['order' => $order]);
        $invoice->run();
        $this->openDownloadableLink($order, (int)$downloads);
    }

    /**
     * Open Downloadable Link
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
