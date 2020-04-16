<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Plugin\Product\Media;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
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
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * @param Json $json
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     */
    public function __construct(
        Json $json,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider
    ) {
        $this->json = $json;
        $this->configurableOptionsProvider = $configurableOptionsProvider;
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
            $products = $this->configurableOptionsProvider->getProducts($parentProduct);
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
