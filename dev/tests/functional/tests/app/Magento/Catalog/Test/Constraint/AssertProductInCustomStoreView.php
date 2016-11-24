<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Store\Test\Fixture\Store;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that product name is correct in Custom adn Default store views.
 */
class AssertProductInCustomStoreView extends AbstractAssertForm
{
    /**
     * Product view.
     *
     * @var CatalogProductView
     */
    private $productViewPage;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Browser.
     *
     * @var CmsIndex
     */
    private $cmsIndexPage;

    /**
     * Assert that product name is correct in Custom adn Default store views.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param FixtureInterface $initialProduct
     * @param Store $store
     * @param CmsIndex $cmsIndexPage
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        FixtureInterface $initialProduct,
        Store $store,
        CmsIndex $cmsIndexPage
    ) {
        $this->browser = $browser;
        $this->cmsIndexPage = $cmsIndexPage;
        $this->productViewPage = $catalogProductView;

        $this->verifyProductOnMainStore($initialProduct);
        $this->verifyProductOnCustomStore($product, $store);
    }

    /**
     * Verify product name in default store view.
     *
     * @param FixtureInterface $initialProduct
     * @return void
     */
    protected function verifyProductOnMainStore(FixtureInterface $initialProduct)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            $initialProduct->getName(),
            $this->productViewPage->getViewBlock()->getProductName(),
            'Product ' . $initialProduct->getName() . ' is incorrect.'
        );
    }

    /**
     * Verify product name in custom store view.
     *
     * @param FixtureInterface $updatedProduct
     * @param Store $store
     * @return void
     */
    protected function verifyProductOnCustomStore(FixtureInterface $updatedProduct, Store $store)
    {
        $this->cmsIndexPage->getStoreSwitcherBlock()->selectStoreView($store->getName());
        $this->cmsIndexPage->getLinksBlock()->waitWelcomeMessage();

        $this->browser->open($_ENV['app_frontend_url'] . $updatedProduct->getUrlKey() . '.html');

            \PHPUnit_Framework_Assert::assertEquals(
                $updatedProduct->getName(),
                $this->productViewPage->getViewBlock()->getProductName(),
                'Product ' . $updatedProduct->getName() . ' is not available on ' . $store->getName() . ' store.'
            );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed correctly in default and custom store views.';
    }
}
