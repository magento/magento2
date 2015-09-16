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
 * 1. Go to product edit in admin
 * 2. Add video to product
 * 3. Save product
 * 4. Make assertions
 *
 */
class AddVideoToPCFTest extends Injectable
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
     * Add video to product.
     *
     * @param CatalogProductSimple $product
     * @param array $video
     * @return array
     */
    public function test(CatalogProductSimple $product, $video)
    {
        // Preconditions
        $product->persist();

        // Steps
        $this->editProductPage->open(['id' => $product->getId()]);
        $this->editProductPage->getProductForm()->openTab('images');
        $imagesTab = $this->editProductPage->getProductForm()->getTab('images');
        $imagesTab->clickAddVideo();
        $newVideoDialog = $imagesTab->getVideoDialog();

        $newVideoDialog->fillVideoUrl($video['video_url']);
        $newVideoDialog->getVideoInfo();
        unset($video['video_url']);
        $newVideoDialog->fill($video);
        $newVideoDialog->add();

        $this->editProductPage->getFormPageActions()->save();

        return $product;
    }
}