<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class CreateConfigurableTest
 * Configurable product
 */
class CreateConfigurableTest extends Functional
{
    /**
     * Creating configurable product and assigning it to category
     *
     * @ZephyrId MAGETWO-12620
     * @return void
     */
    public function testCreateConfigurableProduct()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoConfigurableProductConfigurableProduct();
        $product->switchData('configurable');
        //Page & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getGridPageActionBlock()->addProduct('configurable');
        $createProductPage->getProductForm()->fill($product);
        $createProductPage->getFormPageActions()->save($product);
        //Verifying
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->waitSuccessMessage();
        //Verifying
        $this->assertOnGrid($product);
        $this->assertOnFrontend($product);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    protected function assertOnGrid(ConfigurableProduct $product)
    {
        //Search data
        $configurableSearch = [
            'sku' => $product->getSku(),
            'type' => 'Configurable Product',
        ];
        $variationSkus = $product->getVariationSkus();
        //Page & Block
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Grid */
        $gridBlock = $productGridPage->getProductGrid();
        //Assertion
        $this->assertTrue($gridBlock->isRowVisible($configurableSearch), 'Configurable product was not found.');
        foreach ($variationSkus as $sku) {
            $this->assertTrue(
                $gridBlock->isRowVisible(['sku' => $sku, 'type' => 'Simple Product']),
                'Variation with sku "' . $sku . '" was not found.'
            );
        }
    }

    /**
     * Assert configurable product on Frontend
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    protected function assertOnFrontend(ConfigurableProduct $product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue(
            $productListBlock->isProductVisible($product->getName()),
            'Product is absent on category page.'
        );
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $productListBlock->openProductViewPage($product->getName());
        $this->assertEquals(
            $product->getName(),
            $productViewBlock->getProductName(),
            'Product name does not correspond to specified.'
        );
        $price = $product->getProductPrice();
        $priceOnPage = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(
            number_format($price, 2),
            number_format($priceOnPage, 2),
            'Product price does not correspond to specified.'
        );

        $pageOptions = $productViewBlock->getOptions($product);
        $configurableOptions = [];
        foreach ($pageOptions['configurable_options'] as $attribute) {
            $configurableOption = [];
            foreach ($attribute['options'] as $option) {
                $configurableOption[] = $option['title'];
            }

            $configurableOptions[$attribute['title']] = $configurableOption;
        }
        $this->assertEquals($product->getConfigurableOptions(), $configurableOptions);
    }
}
