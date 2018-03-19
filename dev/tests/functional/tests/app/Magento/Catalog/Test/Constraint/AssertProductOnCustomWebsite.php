<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that Product is present on Custom Website and absent on Main Website.
 */
class AssertProductOnCustomWebsite extends AbstractAssertForm
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
    protected $productViewPage;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert Product is present on Custom Website and absent on Main Website:
     * 1. Product is absent on Main Website.
     * 2. Product is present on Custom Website.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        $this->browser = $browser;
        $this->productViewPage = $catalogProductView;

        $this->verifyProductOnMainWebsite($product);
        $this->verifyProductOnCustomWebsite($product);
    }

    /**
     * Verify Product is absent on Main Website.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function verifyProductOnMainWebsite(FixtureInterface $product)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        \PHPUnit\Framework\Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $this->productViewPage->getTitleBlock()->getTitle(),
            'Product ' . $product->getName() . ' is available on Main Website, but should not.'
        );
    }

    /**
     * Verify Product is present on assigned custom websites.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function verifyProductOnCustomWebsite(FixtureInterface $product)
    {
        $websites = $product->getDataFieldConfig('website_ids')['source']->getWebsites();
        foreach ($websites as $website) {
            $this->browser->open(
                $_ENV['app_frontend_url'] . 'websites/' . $website->getCode() . '/' . $product->getUrlKey() . '.html'
            );

            \PHPUnit\Framework\Assert::assertEquals(
                $product->getName(),
                $this->productViewPage->getViewBlock()->getProductName(),
                'Product ' . $product->getName() . ' is not available on ' . $website->getName() . ' website.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product on product view page displayed in appropriate Website.';
    }
}
