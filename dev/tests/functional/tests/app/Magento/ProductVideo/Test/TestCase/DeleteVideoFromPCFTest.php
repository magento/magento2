<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Steps:
 *
 * 1. Go to product edit in admin and edit a product with video
 * 2. Delete video from product
 * 3. Save product
 * 4. Make assertions
 *
 */
class DeleteVideoFromPCFTest extends Injectable
{
    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    protected $editProductPage;

    /**
     * Injection data.
     *
     * @param CatalogProductEdit $editProductPage
     * @return void
     */
    public function __inject(
        CatalogProductEdit $editProductPage
    ) {
        $this->editProductPage = $editProductPage;
    }

    /**
     * Delete video to product.
     *
     * @param CatalogProductSimple $product
     * @return array
     */
    public function test(CatalogProductSimple $product)
    {
        // Preconditions
        $product = $this->objectManager->create(
            'Magento\ProductVideo\Test\TestStep\CreateProductWithVideoStep',
            ['product' => $product]
        )->run();
        $product = $product['product'];

        // Steps
        $this->editProductPage->open(['id' => $product->getId()]);
        $this->editProductPage->getProductForm()->openTab('images');

        $imagesTab = $this->editProductPage->getProductForm()->getTab('images');
        $imagesTab->clickFirstVideo();

        $videoDialog = $imagesTab->getVideoDialog();
        $videoDialog->delete();

        $this->editProductPage->getFormPageActions()->save();

        return $product;
    }
}