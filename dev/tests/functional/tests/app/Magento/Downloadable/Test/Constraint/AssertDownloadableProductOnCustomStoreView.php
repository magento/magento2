<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Store\Test\Fixture\Store;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that Downloadable Product is present on Custom Store and absent on Main Store.
 */
class AssertDownloadableProductOnCustomStoreView extends AbstractAssertForm
{
    /**
     * Message on the product page 404.
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

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
     * Assert Product is present on Custom Store and absent on Main Store:
     * 1. Product is absent on Main Store.
     * 2. Product is present on Custom Store.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param DownloadableProduct $changedProduct
     * @param Store $store
     * @param CmsIndex $cmsIndexPage
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        DownloadableProduct $changedProduct,
        Store $store,
        CmsIndex $cmsIndexPage
    ) {
        $this->browser = $browser;
        $this->cmsIndexPage = $cmsIndexPage;
        $this->productViewPage = $catalogProductView;

        $this->verifyProductOnMainStore($changedProduct);
        $this->verifyProductOnCustomStore($changedProduct, $store);
    }

    /**
     * Verify Product is absent on Main Store.
     *
     * @param DownloadableProduct $product
     * @return void
     */
    protected function verifyProductOnMainStore(DownloadableProduct $product)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $this->productViewPage->getTitleBlock()->getTitle(),
            'Product ' . $product->getName() . ' is available on Main Store, but should not.'
        );
    }

    /**
     * Verify Product is present on assigned custom store.
     *
     * @param DownloadableProduct $product
     * @param Store $store
     * @return void
     */
    protected function verifyProductOnCustomStore(DownloadableProduct $product, Store $store)
    {
        $this->cmsIndexPage->getStoreSwitcherBlock()->selectStoreView($store->getName());
        $this->cmsIndexPage->getLinksBlock()->waitWelcomeMessage();

        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

            \PHPUnit_Framework_Assert::assertEquals(
                $product->getName(),
                $this->productViewPage->getViewBlock()->getProductName(),
                'Product ' . $product->getName() . ' is not available on ' . $store->getName() . ' store.'
            );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product on product view page displayed in appropriate Store.';
    }
}
