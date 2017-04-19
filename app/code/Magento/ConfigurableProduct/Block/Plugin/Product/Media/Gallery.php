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
 * Class Gallery
 */
class Gallery extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        Json $json,
        array $data = []
    ) {
        $this->json = $json;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionsMediaGalleryDataJson(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        $result = $this->json->unserialize($result);
        if ($this->getProduct()->getTypeId() == 'configurable') {
            /** @var Configurable $productType */
            $productType = $this->getProduct()->getTypeInstance();
            $products = $productType->getUsedProducts($this->getProduct());
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
