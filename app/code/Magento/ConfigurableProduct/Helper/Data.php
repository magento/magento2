<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Helper;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image;

/**
 * Class Data
 *
 * Helper class for getting options
 * @api
 * @since 100.0.2
 */
class Data
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ImageHelper $imageHelper
     * @param UrlBuilder|null $urlBuilder
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        ImageHelper $imageHelper,
        UrlBuilder $urlBuilder = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->imageHelper = $imageHelper;
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param ProductInterface $product
     * @return Image[]|null
     */
    public function getGalleryImages(ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            /** @var $image Image */
            foreach ($images as $image) {
                $smallImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_small');
                $image->setData('small_image_url', $smallImageUrl);

                $mediumImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_medium');
                $image->setData('medium_image_url', $mediumImageUrl);

                $largeImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_large');
                $image->setData('large_image_url', $largeImageUrl);
            }
        }

        return $images;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if ($this->canDisplayShowOutOfStockStatus()) {
                    if ($product->isSalable()) {
                        $options['salable'][$productAttributeId][$attributeValue][] = $productId;
                    }
                    $options[$productAttributeId][$attributeValue][] = $productId;
                } else {
                    if ($product->isSalable()) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        $options['canDisplayShowOutOfStockStatus'] = $this->canDisplayShowOutOfStockStatus();
        return $options;
    }

    /**
     * Get allowed attributes
     *
     * @param Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return ($product->getTypeId() == Configurable::TYPE_CODE)
            ? $product->getTypeInstance()->getConfigurableAttributes($product)
            : [];
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function canDisplayShowOutOfStockStatus(): bool
    {
        return (bool) $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock');
    }
}
