<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 * 1. Create bundle product with options.
 * 2. Go to frontend product detail page of a bundle product.
 * 3. Configure bundle product by selecting bundle option selections.
 * 4. Add a bundle product to cart.
 * 5. Go to  backend and open the product edit page for the product which has been added to the cart.
 * 6. Change one from the option title and save product.
 * 7. Open shopping cart again.
 * 8. Verify shopping cart.
 *
 * @group Bundle_Product
 * @ZephyrId MAGETWO-69469
 */
class UpdateBundleOptionsShoppingCartTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Page product on backend
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductEdit $catalogProductEditPage
     * @param TestStepFactory $testStepFactory
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndexNewPage,
        CatalogProductEdit $catalogProductEditPage,
        TestStepFactory $testStepFactory
    ) {
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
        $this->testStepFactory = $testStepFactory;
    }
    
    /**
     * Test update bundle product option title after adding to the shopping cart
     *
     * @param BundleProduct $originalProduct
     * @param string $optionTitle
     * @param string $optionNumber
     * @return void
     */
    public function test(BundleProduct $originalProduct, $optionTitle, $optionNumber)
    {
        // Create product
        $originalProduct->persist();
        // Add product to the shopping cart
        $productData = ['products' => $originalProduct];
        $addToCartStep = $this->testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $productData]
        );
        $addToCartStep->run();
        // Change bundle option title
        $filter = ['sku' => $originalProduct->getSku()];
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('bundle');
        /** @var  \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle $container */
        $container = $form->getSection('bundle');
        $container->changeOptionTitle($optionTitle, $optionNumber);
        $this->catalogProductEdit->getFormPageActions()->save();
    }
}
