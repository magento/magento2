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
 * @since 2.2.0
 */
class Gallery
{
    /**
     * @var Json
     * @since 2.2.0
     */
    private $json;

    /**
     * @param Json $json
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
