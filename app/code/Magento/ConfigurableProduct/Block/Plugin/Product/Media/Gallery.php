<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Plugin\Product\Media;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Provides a serialized media gallery data for configurable product options.
 */
class Gallery
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param string $result
     * @return string
     */
    public function afterGetOptionsMediaGalleryDataJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        $result = $this->json->unserialize($result);
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
        return $this->json->serialize($result);
    }

    /**
     * @param Product $product
     * @return array
     */
    private function getProductGallery(Product $product): array
    {
        $result = [];
        $images = $this->getImagesOrderedByPosition($product);
        foreach ($images as $image) {
            $result[] = [
                'mediaType' => $image->getMediaType(),
                'videoUrl' => $image->getVideoUrl(),
                'isBase' => $product->getImage() == $image->getFile(),
            ];
        }
        return $result;
    }

    /**
     * @param Product $product
     * @return array
     */
    private function getImagesOrderedByPosition(Product $product): array
    {
        $imagesCollection = $product->getMediaGalleryImages();
        $images = $imagesCollection->getItems();
        usort($images, function ($el1, $el2) {
            return $el1['position'] <=> $el2['position'];
        });
        return $images;
    }
}
