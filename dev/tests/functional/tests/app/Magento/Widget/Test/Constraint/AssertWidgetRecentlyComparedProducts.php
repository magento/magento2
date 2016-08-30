<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that widget with type Recently Compared Products is present on Product Compare page
 */
class AssertWidgetRecentlyComparedProducts extends AbstractConstraint
{
    /**
     * Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Catalog product compare page
     *
     * @var CatalogProductCompare
     */
    protected $catalogProductCompare;

    /**
     * Catalog product page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Assert that widget with type Recently Compared Products is present on Product Compare page
     *
     * @param CatalogProductCompare $catalogProductCompare
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param Widget $widget
     * @param CatalogProductSimple $productSimple1
     * @param CatalogProductSimple $productSimple2
     * @param Cache $cache
     * @var string
     * @return void
     */
    public function processAssert(
        CatalogProductCompare $catalogProductCompare,
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        Widget $widget,
        CatalogProductSimple $productSimple1,
        CatalogProductSimple $productSimple2,
        Cache $cache
    ) {
        // Flush cache
        $cache->flush();

        $this->catalogProductCompare = $catalogProductCompare;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->cmsIndex = $cmsIndex;

        $productSimple1->persist();
        $products[] = $productSimple1;
        $productSimple2->persist();
        $products[] = $productSimple2;

        $cmsIndex->open();
        $this->addProducts($products);
        $this->removeCompareProducts();

        \PHPUnit_Framework_Assert::assertTrue(
            $this->catalogProductCompare->getWidgetView()->isWidgetVisible($widget, 'Recently Compared'),
            'Widget is absent on Product Compare page.'
        );
    }

    /**
     * Add products to compare list
     *
     * @param array $products
     * @return void
     */
    protected function addProducts(array $products)
    {
        foreach ($products as $itemProduct) {
            $this->browser->open($_ENV['app_frontend_url'] . $itemProduct->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->clickAddToCompare();
            $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        }
    }

    /**
     * Remove compare product
     *
     * @return void
     */
    protected function removeCompareProducts()
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink("Compare Products");
        $this->catalogProductCompare->getCompareProductsBlock()->removeAllProducts();
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Widget with type Recently Compared Products is present on Product Compare page";
    }
}
