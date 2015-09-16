<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create product with video step.
 */
class CreateProductWithVideoStep implements TestStepInterface
{

    /**
     * Temporary media path
     *
     * @var string
     */
    protected $mediaPathTmp = __DIR__.'/../../../../../../../../../pub/media/tmp/catalog/product';

    /**
     * Product object.
     *
     * @var CatalogProductSimple
     */
    protected $product;


    /**
     * Preparing step properties.
     *
     * @constructor
     * @param CatalogProductSimple $product
     */
    public function __construct(CatalogProductSimple $product)
    {
        $this->product = $product;
    }

    /**
     * Create product with video.
     *
     * @return array
     */
    public function run()
    {
        $product = $this->product;
        $gallery = $product->getMediaGallery();
        if (isset($gallery['images']) && is_array($gallery['images']) && count($gallery['images'])) {
            $image = reset($gallery['images']);
            if (isset($image['file'])) {
                $this->createTestImage($image['file']);
            }
        }

        $product->persist();
        return ['product' => $product];
    }

    protected function createTestImage($filename)
    {
        $filename = $this->getFullPath($filename);
        if (!file_exists($filename)) {
            // Create an image with the specified dimensions
            $image = imageCreate(300,200);

            // Create a color (this first call to imageColorAllocate
            //  also automatically sets the image background color)
            $colorRed = imageColorAllocate($image, 255,0,0);
            // Create another color
            $colorYellow = imageColorAllocate($image, 255,255,0);

            // Draw a rectangle
            imageFilledRectangle($image, 50, 50, 250, 150, $colorYellow);

            if (!file_exists(dirname($filename))) {
                mkdir($filename, 0777, true);
            }
            imageJpeg($image, $filename);

            // Release memory
            imageDestroy($image);
        }

    }

    /**
     * Gets full path based on filename.
     *
     * @param string $filename
     * @return string
     */
    protected function getFullPath($filename)
    {
        return $this->mediaPathTmp . $filename;
    }
}
