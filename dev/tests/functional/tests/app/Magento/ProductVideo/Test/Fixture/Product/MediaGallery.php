<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;

/**
 * Media Gallery data source.
 *
 * Data keys:
 *  - dataset (Price verification dataset name)
 *  - value (Price value)
 */
class MediaGallery extends DataSource
{
    /**
     * Temporary media path
     *
     * @var string
     */
    protected $mediaPathTmp = '/pub/media/tmp/catalog/product';

    /**
     * @constructor
     * @param array $params
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $params, $data = [])
    {
        if (isset($data['images']) && is_array($data['images']) && count($data['images'])) {
            foreach ($data['images'] as $media) {
                if (isset($media['file'])) {
                    $this->createTestImage($media['file']);
                }
            }
        }
        $this->data = $data;
    }

    /**
     * Create test image.
     *
     * @param string $filename
     * @return void
     */
    protected function createTestImage($filename)
    {
        $filename = $this->getFullPath($filename);
        if (!file_exists($filename)) {
            // Create an image with the specified dimensions
            $image = imageCreate(300, 200);

            // Create a color (this first call to imageColorAllocate
            //  also automatically sets the image background color)
            $colorYellow = imageColorAllocate($image, 255, 255, 0);

            // Draw a rectangle
            imageFilledRectangle($image, 50, 50, 250, 150, $colorYellow);

            $directory = dirname($filename);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
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
        return BP . $this->mediaPathTmp . $filename;
    }
}
