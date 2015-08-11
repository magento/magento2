<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

class VariationHandler
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Backend\Media */
    protected $media;

    /**
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Media $media
     */
    public function __construct(\Magento\Catalog\Model\Product\Attribute\Backend\Media $media)
    {
        $this->media = $media;
    }

    /**
     * Duplicate images for variations
     *
     * @param array $productsData
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function duplicateImagesForVariations($productsData)
    {
        $imagesForCopy = [];
        foreach ($productsData as $variationId => $simpleProductData) {
            if (!isset($simpleProductData['media_gallery']['images'])) {
                continue;
            }

            foreach ($simpleProductData['media_gallery']['images'] as $imageId => $image) {
                $image['variation_id'] = $variationId;
                if (isset($imagesForCopy[$imageId][0])) {
                    // skip duplicate image for first product
                    unset($imagesForCopy[$imageId][0]);
                }
                $imagesForCopy[$imageId][] = $image;
            }
        }
        foreach ($imagesForCopy as $imageId => $variationImages) {
            foreach ($variationImages as $image) {
                $file = $image['file'];
                $variationId = $image['variation_id'];
                $newFile = $this->media->duplicateImageFromTmp($file);
                $productsData[$variationId]['media_gallery']['images'][$imageId]['file'] = $newFile;
                foreach (['small_image', 'thumbnail', 'image'] as $imageType) {
                    if (isset($productsData[$variationId][$imageType])
                        && $productsData[$variationId][$imageType] == $file
                    ) {
                        $productsData[$variationId][$imageType] = $newFile;
                    }
                }
            }
        }
        return $productsData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productData
     *
     * @return array
     */
    public function processMediaGallery($product, $productData)
    {
        if (!empty($productData['image'])) {
            $image = $productData['image'];
            if (!isset($productData['media_gallery']['images'])) {
                $productData['media_gallery']['images'] = [];
            }
            if (false === array_search($image, array_column($productData['media_gallery']['images'], 'file'))) {
                $productData['small_image'] = $productData['thumbnail'] = $image;
                $productData['media_gallery']['images'][] = [
                    'position' => 1,
                    'file' => $image,
                    'disabled' => 0,
                    'label' => '',
                ];
            }
        }
        if ($product->getMediaGallery('images') && !empty($productData['media_gallery']['images'])) {
            $gallery = array_map(
                function ($image) {
                    $image['removed'] = 1;
                    return $image;
                },
                $product->getMediaGallery('images')
            );
            $gallery = array_merge($productData['media_gallery']['images'], $gallery);
            $productData['media_gallery']['images'] = $gallery;
        }
        return $productData;
    }
}
