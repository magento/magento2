<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;
use Mtf\TestCase\Injectable;

/**
 * Abstract class for compare products class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCompareProductsTest extends Injectable
{
    /**
     * Array products.
     *
     * @var array
     */
    protected $products;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Browser.
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Catalog product compare page.
     *
     * @var CatalogProductCompare
     */
    protected $catalogProductCompare;

    /**
     * Catalog product page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Customer login page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Fixture customer.
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerInjectable $customer
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory, CustomerInjectable $customer)
    {
        $this->fixtureFactory = $fixtureFactory;
        $customer->persist();
        $this->customer = $customer;
    }

    /**
     * Injection data.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        Browser $browser,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->browser = $browser;
    }

    /**
     * Login customer.
     *
     * @return void
     */
    protected function loginCustomer()
    {
        if (!$this->cmsIndex->getLinksBlock()->isLinkVisible('Log Out')) {
            $this->cmsIndex->getLinksBlock()->openLink("Log In");
            $this->customerAccountLogin->getLoginBlock()->login($this->customer);
        }
    }

    /**
     * Create products.
     *
     * @param string $products
     * @return array
     */
    protected function createProducts($products)
    {
        $products = explode(',', $products);
        foreach ($products as $key => $product) {
            list($fixture, $dataSet) = explode('::', $product);
            $product = $this->fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
            $product->persist();
            $products[$key] = $product;
        }
        return $products;
    }

    /**
     * Add products to compare list.
     *
     * @param array $products
     * @param AbstractConstraint $assert
     * @return void
     */
    protected function addProducts(array $products, AbstractConstraint $assert = null)
    {
        foreach ($products as $itemProduct) {
            $this->browser->open($_ENV['app_frontend_url'] . $itemProduct->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->clickAddToCompare();
            if ($assert !== null) {
                $this->productCompareAssert($assert, $itemProduct);
            }
        }
    }

    /**
     * Perform assert.
     *
     * @param AbstractConstraint $assert
     * @param InjectableFixture $product
     * @return void
     */
    protected function productCompareAssert(AbstractConstraint $assert, InjectableFixture $product)
    {
        $assert->configure(['catalogProductView' => $this->catalogProductView, 'product' => $product]);
        \PHPUnit_Framework_Assert::assertThat($this->getName(), $assert);
    }
}
