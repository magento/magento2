<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Reports\Test\Page\Adminhtml\ProductReportView;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Preconditions:
 * 1. Create products
 * 2. Open product page on frontend
 * 3. Refresh statistic
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports> Products> Views
 * 3. Select time range, report period
 * 4. Click "Show report"
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-27954
 */
class ViewedProductsReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const STABLE = 'no';
    /* end tags */

    /**
     * Product Report View page
     *
     * @var ProductReportView
     */
    protected $productReportView;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Browser interface
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Catalog product index page
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndexPage;

    /**
     * Catalog product index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Inject pages
     *
     * @param CmsIndex $cmsIndex
     * @param ProductReportView $productReportView
     * @param FixtureFactory $fixtureFactory
     * @param BrowserInterface $browser
     * @param CatalogProductIndex $catalogProductIndexPage
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        ProductReportView $productReportView,
        FixtureFactory $fixtureFactory,
        BrowserInterface $browser,
        CatalogProductIndex $catalogProductIndexPage
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->productReportView = $productReportView;
        $this->fixtureFactory = $fixtureFactory;
        $this->browser = $browser;
        $this->catalogProductIndexPage = $catalogProductIndexPage;
    }

    /**
     * Viewed product report list
     *
     * @param string $products
     * @param array $viewsReport
     * @param string $total
     * @return array
     */
    public function test($products, array $viewsReport, $total)
    {
        // Preconditions
        $this->catalogProductIndexPage->open();
        $this->catalogProductIndexPage->getProductGrid()->massaction([], 'Delete', true, 'Select All');
        $productsList = $this->prepareProducts($products);
        $this->openProducts($productsList, $total);
        $this->productReportView->open();
        $this->productReportView->getMessagesBlock()->clickLinkInMessage('notice', 'here');

        // Steps
        $this->productReportView->getFilterBlock()->viewsReport($viewsReport);
        $this->productReportView->getActionsBlock()->showReport();
        return ['productsList' => $productsList];
    }

    /**
     * Create products
     *
     * @param string $productList
     * @return array
     */
    protected function prepareProducts($productList)
    {
        $productsData = explode(', ', $productList);
        $products = [];
        foreach ($productsData as $productConfig) {
            $product = explode('::', $productConfig);
            $productFixture = $this->fixtureFactory->createByCode($product[0], ['dataset' => $product[1]]);
            $productFixture->persist();
            $products[] = $productFixture;
        }
        return $products;
    }

    /**
     * Open products
     *
     * @param array $products
     * @param string $total
     * @return void
     */
    protected function openProducts(array $products, $total)
    {
        $total = explode(', ', $total);
        foreach ($products as $key => $product) {
            for ($i = 0; $i < $total[$key]; $i++) {
                $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
                $this->assertEquals(
                    $product->getName(),
                    $this->cmsIndex->getTitleBlock()->getTitle(),
                    'Could not open product page.'
                );
            }
        }
    }
}
