<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Preconditions:
 * 1. Create a second website.
 * 2. Create a product that is assigned to the default main website.
 *
 * Steps:
 * 1. Open category page in the 2nd website.
 * 2. Reload the category page in the 2nd website.
 * 3. Go to Admin and update product so that it is assigned to the 2nd website.
 * 4. Perform asserts.
 *
 * @ZephyrId MAGETWO-52862
 */
class CacheInvalidationTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    private $editProductPage;

    /**
     * Preparing pages for test.
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductEdit $editProductPage
     * @return void
     */
    public function __inject(
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductEdit $editProductPage
    ) {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->editProductPage = $editProductPage;
    }

    /**
     * Open category on the 2nd website and reassign product.
     *
     * @param CatalogProductSimple $product
     * @param Store $store
     * @return void
     */
    public function test(
        CatalogProductSimple $product,
        Store $store
    ) {
        //Preconditions:
        $product->persist();
        $store->persist();

        //Steps
        $category = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $storeGroup = $store->getDataFieldConfig('group_id')['source']->getStoreGroup();
        $website = $storeGroup->getDataFieldConfig('website_id')['source']->getWebsite();
        $url = $_ENV['app_frontend_url'] . 'websites/' . $website->getCode() . '/' . $category->getUrlKey() . '.html';
        $this->browser->open($url);
        $this->browser->open($url);

        $productFixture = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => ['website_ids' => [['store' => $store]]]]
        );
        $this->editProductPage->open(['id' => $product->getId()]);
        $this->editProductPage->getProductForm()->fill($productFixture);
        $this->editProductPage->getFormPageActions()->save();

        return [
            'category' => $category,
        ];
    }
}
