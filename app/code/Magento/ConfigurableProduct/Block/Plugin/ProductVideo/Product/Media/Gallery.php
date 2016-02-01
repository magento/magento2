<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Block\Plugin\ProductVideo\Product\Media;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Gallery
 */
class Gallery extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface
     */
    protected $galleryManagement;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $galleryManagementInterface
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $galleryManagementInterface,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->galleryManagement = $galleryManagementInterface;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * @param \Magento\ProductVideo\Block\Product\View\Gallery $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionsMediaGalleryDataJson(
        \Magento\ProductVideo\Block\Product\View\Gallery $subject,
        $result
    ) {
        $result = $this->jsonDecoder->decode($result);
        if ($this->getProduct()->getTypeId() == 'configurable') {
            /** @var Configurable $productType */
            $productType = $this->getProduct()->getTypeInstance();
            $products = $productType->getUsedProducts($this->getProduct());
            $attributes = $productType->getConfigurableAttributesAsArray($this->getProduct());
            /** @var \Magento\Catalog\Model\Product $product */
            foreach($attributes as $attribute) {
                foreach ($products as $product) {
                    $attributeValue = $product->getData($attribute['attribute_code']);
                    if ($attributeValue) {
                        $key = $attribute['attribute_code'] . '_' . $attributeValue;
                        $result[$key] = $this->getProductGallery($product);
                    }
                }
            }
        }
        return $this->jsonEncoder->encode($result);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getProductGallery($product)
    {
        $result = [];
        $product->setMediaGalleryEntries($this->galleryManagement->getList($product->getSku()));
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
