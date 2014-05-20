<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\TestCase\Product\Configurable;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\ProductAttribute;
use Magento\Catalog\Test\Fixture\ConfigurableProduct;

/**
 * Configurable product with creating new category and new attribute
 *
 */
class CreateWithAttributeTest extends Functional
{
    /**
     * Login into backend area before test
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Creating configurable product with creating new category and new attribute (required fields only)
     *
     * @ZephyrId MAGETWO-13361
     */
    public function testCreateConfigurableProductWithNewAttribute()
    {
        //Data
        $product = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $product->switchData('simple_with_new_category');

        $attribute = Factory::getFixtureFactory()->getMagentoCatalogProductAttribute();
        $attribute->switchData('new_attribute');

        $variations = Factory::getFixtureFactory()->getMagentoCatalogConfigurableProduct();
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
     */
    protected function fillSimpleProductWithNewCategory($product)
    {
        //Page & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productBlockForm = $createProductPage->getProductBlockForm();

        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getProductBlock()->addProduct();
        $productBlockForm->fill($product);
        $productBlockForm->openTab(Product::GROUP_PRODUCT_DETAILS);
        $productBlockForm->addNewCategory($product);
    }

    /**
     * Add new attribute to product
     *
     * @param ProductAttribute $attribute
     */
    protected function addNewAttribute(ProductAttribute $attribute)
    {
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();

        $productBlockForm = $createProductPage->getProductBlockForm();
        $productBlockForm->openVariationsTab();
        $productBlockForm->clickCreateNewVariationSet();

        $newAttributeForm = $createProductPage->getAttributeEditBlock();
        $this->assertTrue($newAttributeForm->isVisible(), '"New attribute" window is not opened');

        $newAttributeForm->openFrontendProperties();
        $newAttributeForm->fill($attribute);
        $newAttributeForm->saveAttribute();
        $createProductPage->switchToMainPage();
    }

    /**
     * Fill product variations and save product
     *
     * @param ConfigurableProduct $variations
     */
    protected function fillProductVariationsAndSave(ConfigurableProduct $variations)
    {
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productBlockForm = $createProductPage->getProductBlockForm();
        $productBlockForm->fillVariations($variations);
        $productBlockForm->save($variations);
    }

    /**
     * Assert product was saved
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
     */
    protected function assertOnGrid(Product $product)
    {
        $configurableSearch = array(
            'sku' => $product->getProductSku(),
            'type' => 'Configurable Product',
        );
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
            $productListBlock->isProductVisible($product->getProductName()),
            'Product is absent on category page.'
        );
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $productListBlock->openProductViewPage($product->getProductName());
        $this->assertEquals(
            $product->getProductName(),
            $productViewBlock->getProductName(),
            'Product name does not correspond to specified.'
        );
        $this->assertEquals(
            $product->getProductPrice(),
            $productViewBlock->getProductPrice(),
            'Product price does not correspond to specified.'
        );
        $this->assertTrue(
            $productViewBlock->verifyProductOptions($variations),
            'Added configurable options are absent.'
        );
    }
}
