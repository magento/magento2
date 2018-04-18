<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Plugin\Product\Media;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;

/**
 * Provides a serialized media gallery data for configurable product options.
 */
class Gallery
{
    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param string $result
     * @return string
     */
    public function afterGetOptionsMediaGalleryDataJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        $result = json_decode($result, true);
        $parentProduct = $subject->getProduct();
        if ($parentProduct->getTypeId() == Configurable::TYPE_CODE) {
            /** @var Configurable $productType */
            $productType = $parentProduct->getTypeInstance();
            $products = $productType->getUsedProducts($parentProduct);
            /** @var Product $product */
            foreach ($products as $product) {
                $key = $product->getId();
                $result[$key] = $this->getProductGallery($product);
            }
        }
        return json_encode($result);
    }

    /**
     * @param Product $product
     * @return array
     */
    private function getProductGallery($product)
    {
        $result = [];
        $images = $product->getMediaGalleryImages();
        foreach ($images as $image) {
            $result[] = [
                'mediaType' => $image->getMediaType(),
                'videoUrl' => $image->getVideoUrl(),
                'isBase' => $product->getImage() == $image->getFile(),
            ];
        }
        return $result;
    }
}
