<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\TestCase\Injectable;

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
     * @var BrowserInterface
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
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Fixture customer.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory, Customer $customer)
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
     * @param BrowserInterface $browser
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
    }

    /**
     * Login customer.
     *
     * @return void
     */
    protected function loginCustomer()
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
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
            list($fixture, $dataset) = explode('::', $product);
            $product = $this->fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
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
