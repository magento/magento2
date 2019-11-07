<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\TestStep\CreateProductsStep;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep;
use Magento\Mtf\Util\Command\Cli\Config;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create products.
 * 3. Add products to compare list.
 * 4. Clear compare list.
 *
 * Steps:
 * 1. Open Customers > All Customers.
 * 2. Search and open customer from preconditions.
 * 3. Click 'Create Order'.
 * 4. Check product in 'Recently compared List' section.
 * 5. Click 'Update Changes'.
 * 6. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-28109
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoveRecentlyComparedProductsOnOrderPageTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Catalog product page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Customer index page.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer index edit page.
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Catalog product compare page.
     *
     * @var CatalogProductCompare
     */
    protected $catalogProductCompare;

    /**
     * @var Config
     */
    private $config;

    /**
     * Create customer.
     * @param Customer $customer
     * @param BrowserInterface $browser
     * @param Config $config
     * @return array
     */
    public function __prepare(Customer $customer, BrowserInterface $browser, Config $config)
    {
        $customer->persist();
        // Login under customer
        $this->objectManager
            ->create(LoginCustomerOnFrontendStep::class, ['customer' => $customer])
            ->run();
        $this->browser = $browser;
        $this->config = $config;

        return ['customer' => $customer];
    }

    /**
     * Inject pages.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param OrderCreateIndex $orderCreateIndex
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CatalogProductCompare $catalogProductCompare
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        OrderCreateIndex $orderCreateIndex,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        CatalogProductCompare $catalogProductCompare
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->orderCreateIndex = $orderCreateIndex;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->catalogProductCompare = $catalogProductCompare;
    }

    public function setUp()
    {
        $this->config->setConfig('reports/options/enabled', 1);
        parent::setUp();
    }

    /**
     * Move recently compared products on order page.
     * @param Customer $customer
     * @param string $products
     * @param bool $productsIsConfigured
     * @return array
     * @throws \Exception
     */
    public function test(Customer $customer, $products, $productsIsConfigured = false)
    {
        // Preconditions
        // Create product
        $products = $this->objectManager->create(
            CreateProductsStep::class,
            ['products' => $products]
        )->run()['products'];
        foreach ($products as $itemProduct) {
            $this->browser->open($_ENV['app_frontend_url'] . $itemProduct->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->clickAddToCompare();
        }
        $this->cmsIndex->getLinksBlock()->openLink("Compare Products");
        $this->catalogProductCompare->getCompareProductsBlock()->removeAllProducts();

        // Steps:
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $this->customerIndexEdit->getPageActionsBlock()->createOrder();
        $this->orderCreateIndex->getStoreBlock()->selectStoreView();
        $activitiesBlock = $this->orderCreateIndex->getCustomerActivitiesBlock();
        $activitiesBlock->getRecentlyComparedProductsBlock()->addProductsToOrder($products);
        $activitiesBlock->updateChanges();

        return ['products' => $products, 'productsIsConfigured' => $productsIsConfigured];
    }

    public function tearDown()
    {
        $this->config->setConfig('reports/options/enabled', 0);
        parent::tearDown();
    }
}
