<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\Product;

/**
 * Class Media
 *
 * @package Magento\Swatches\Controller\Ajax
 */
class Media extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * @param Context $context
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     */
    public function __construct(
        Context $context,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Catalog\Model\ProductFactory $productModelFactory
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->productModelFactory = $productModelFactory;

        parent::__construct($context);
    }

    /**
     * Get product media by fallback:
     * 1stly by default attribute values
     * 2ndly by getting base image from configurable product
     *
     * @return string
     */
    public function execute()
    {
        $productMedia = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $currentConfigurable = $this->productModelFactory->create()->load($productId);
            $attributes = (array)$this->getRequest()->getParam('attributes');
            if (!empty($attributes)) {
                $product = $this->getProductVariationWithMedia($currentConfigurable, $attributes);
            }
            if ((empty($product) || (!$product->getImage() || $product->getImage() == 'no_selection'))
                && isset($currentConfigurable)
            ) {
                $product = $currentConfigurable;
            }
            $productMedia = $this->swatchHelper->getProductMediaGallery($product);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
        return $resultJson;
    }

    /**
     * @param Product $currentConfigurable
     * @param array $attributes
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface|null
     */
    protected function getProductVariationWithMedia(Product $currentConfigurable, array $attributes)
    {
        $product = null;
        $layeredAttributes = [];
        $configurableAttributes = $this->swatchHelper->getAttributesFromConfigurable($currentConfigurable);
        if ($configurableAttributes) {
            $layeredAttributes = $this->getLayeredAttributesIfExists($configurableAttributes);
        }
        $resultAttributes = array_merge($layeredAttributes, $attributes);

        $product = $this->swatchHelper->loadVariationByFallback($currentConfigurable, $resultAttributes);
        if (!$product || (!$product->getImage() || $product->getImage() == 'no_selection')) {
            $product = $this->swatchHelper->loadFirstVariationWithSwatchImage($currentConfigurable, $resultAttributes);
        }
        if (!$product) {
            $product = $this->swatchHelper->loadFirstVariationWithImage($currentConfigurable, $resultAttributes);
        }
        return $product;
    }

    /**
     * @param array $configurableAttributes
     * @return array
     */
    protected function getLayeredAttributesIfExists(array $configurableAttributes)
    {
        $layeredAttributes = [];

        foreach ($configurableAttributes as $attribute) {
            if ($urlAdditional = (array)$this->getRequest()->getParam('additional')) {
                if (array_key_exists($attribute['attribute_code'], $urlAdditional)) {
                    $layeredAttributes[$attribute['attribute_code']] = $urlAdditional[$attribute['attribute_code']];
                }
            }
        }
        return $layeredAttributes;
    }
}
