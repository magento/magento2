<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\ProductAttribute;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class CreateWithAttributeTest
 * Configurable product with creating new category and new attribute
 */
class CreateWithAttributeTest extends Functional
{
    /**
     * Creating configurable product with creating new category and new attribute (required fields only)
     *
     * @ZephyrId MAGETWO-13361
     * @return void
     */
    public function testCreateConfigurableProductWithNewAttribute()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple_with_new_category');

        $attribute = Factory::getFixtureFactory()->getMagentoCatalogProductAttribute();
        $attribute->switchData('new_attribute');

        $variations = Factory::getFixtureFactory()->getMagentoConfigurableProductConfigurableProduct();
        $variations->switchData('product_variations');
        $variations->provideNewAttributeData($attribute);

        //Steps
        $this->fillSimpleProductWithNewCategory($product);
        $this->addNewAttribute($attribute);
        $this->fillProductVariationsAndSave($variations);

        //Verifying
        $this->assertProductSaved();
        $this->assertOnGrid($product);
        $this->assertOnFrontend($product, $variations);
    }

    /**
     * Fill required fields for simple product with category creation
     *
     * @param Product $product
     * @return void
     */
    protected function fillSimpleProductWithNewCategory(Product $product)
    {
        //Page & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductEdit();
        $productForm = $createProductPage->getProductForm();

        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getGridPageActionBlock()->addProduct();
        $productForm->fill($product);
        $productForm->openTab(Product::GROUP_PRODUCT_DETAILS);
        $productForm->addNewCategory($product);
    }

    /**
     * Add new attribute to product
     *
     * @param ProductAttribute $attribute
     * @return void
     */
    protected function addNewAttribute(ProductAttribute $attribute)
    {
        $attributeData = $attribute->getData();
        $attributeFields = [];
        foreach ($attributeData['fields'] as $name => $field) {
            $attributeFields[$name] = $field['value'];
        }
        $attributeFields['options'] = $attributeData['options']['value'];

        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        $productForm->openTab('variations');

        /** @var \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config $variations */
        $variations = $productForm->getTabElement('variations');
        $variations->showContent();
        $variations->getAttributeBlock()->fillAttributes([$attributeFields]);
    }

    /**
     * Fill product variations and save product
     *
     * @param ConfigurableProduct $variations
     * @return void
     */
    protected function fillProductVariationsAndSave(ConfigurableProduct $variations)
    {
        $variationsData = $variations->getData();
        $matrix = [];
        foreach ($variationsData['fields']['variations-matrix']['value'] as $variation) {
            $matrix[] = [
                'quantity_and_stock_status' => [
                    'qty' => $variation['value']['qty']['value'],
                ],
            ];
        }

        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        $productForm->openTab('variations');

        /** @var \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config $variationsTab */
        $variationsTab = $productForm->getTabElement('variations');
        $variationsTab->generateVariations();
        $variationsTab->getVariationsBlock()->fillVariations($matrix);

        $createProductPage->getFormPageActions()->save($variations);
    }

    /**
     * Assert product was saved
     *
     * @return void
     */
    protected function assertProductSaved()
    {
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $this->assertEquals(
            'You saved the product.',
            $createProductPage->getMessagesBlock()->getSuccessMessages(),
            'Product was not saved'
        );
    }

    /**
     * Assert existing product on backend product grid
     *
     * @param Product $product
     * @return void
     */
    protected function assertOnGrid(Product $product)
    {
        $configurableSearch = [
            'sku' => $product->getSku(),
            'type' => 'Configurable Product',
        ];
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible($configurableSearch), 'Configurable product was not found.');
    }

    /**
     * Assert configurable product on frontend
     *
     * @param Product $product
     * @param ConfigurableProduct $variations
     * @return void
     */
    protected function assertOnFrontend(Product $product, ConfigurableProduct $variations)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getNewCategoryName());
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
        $price = $productViewBlock->getPriceBlock()->getPrice();
        $this->assertEquals(
            number_format($product->getProductPrice(), 2),
            $price,
            'Product price does not correspond to specified.'
        );

        $pageOptions = $productViewBlock->getOptions($variations);
        $configurableOptions = [];
        foreach ($pageOptions['configurable_options'] as $attribute) {
            $configurableOption = [];
            foreach ($attribute['options'] as $option) {
                $configurableOption[] = $option['title'];
            }

            $configurableOptions[$attribute['title']] = $configurableOption;
        }
        $this->assertEquals($variations->getConfigurableOptions(), $configurableOptions);
    }
}
