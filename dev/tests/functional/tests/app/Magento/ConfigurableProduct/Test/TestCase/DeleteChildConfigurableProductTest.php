<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Constraint\AssertProductSaveMessage as assertProductSaveMessage;
use Magento\Mtf\TestCase\Injectable;

/**
 * Verify if configurable product shows up on frontEnd after children are deleted
 *
 * 1. Go to Backend
 * 2. Open Product -> Catalog
 * 3. Click on narrow near "Add Product" button
 * 4. Select Configurable Product
 * 5. Fill in data according to data sets
 * 6. Save the product.
 * 7. From the product grid, select the child products and mass delete them.
 * 6. Navigate to the frontend
 * 7. Verify that the product is not available on the category page.
 * 8. Verify that product is displayed on frontend through direct url
 */
class DeleteChildConfigurableProductTest extends Injectable
{
    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to create a product.
     *
     * @var CatalogProductNew
     */
    protected $productNew;

    /**
     * Assert Invalid Date error message.
     *
     * @var AssertProductSaveMessage
     */
    private $assertProductSaveMessage;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    private $editProductPage;

    /**
     * @param CatalogProductNew $productNew
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param assertProductSaveMessage $assertProductSaveMessage
     */
    public function __inject(
        CatalogProductNew $productNew,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        AssertProductSaveMessage $assertProductSaveMessage
    ) {
        $this->productGrid = $productGrid;
        $this->productNew = $productNew;
        $this->editProductPage = $editProductPage;
        $this->assertProductSaveMessage = $assertProductSaveMessage;
    }

    /**
     * @param ConfigurableProduct $product
     * @return array
     */
    public function test(ConfigurableProduct $product)
    {
        $deleteProducts = [];
        $this->productGrid->open();
        $this->productGrid->getGridPageActionBlock()->addProduct('configurable');
        $this->productNew->getProductForm()->fill($product);
        $this->productNew->getFormPageActions()->save($product);
        $this->assertProductSaveMessage->processAssert($this->editProductPage);

        $configurableAttributesData = $product->getConfigurableAttributesData();
        $this->productGrid->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $filter = ['name' => $variation['name']];
            $this->productGrid->getProductGrid()->search($filter);
            $itemId = $this->productGrid->getProductGrid()->getFirstItemId();
            $deleteProducts[] = [$this->productGrid->getProductGrid()->getColumnValue($itemId, 'SKU')];
        }
        $this->productGrid->open();

        $this->productGrid->getProductGrid()->massaction($deleteProducts, 'Delete', true);
        return ['product'=> $product];
    }
}
