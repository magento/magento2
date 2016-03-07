<?php
/**
 * Product initialization helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Framework\App\RequestInterface;

/**
 * Class Configurable
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable
{
    /**
     * @var VariationHandler
     */
    private $variationHandler;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Factory
     */
    private $optionsFactory;

    /**
     * Constructor
     *
     * @param VariationHandler $variationHandler
     * @param RequestInterface $request
     * @param Factory $optionsFactory
     */
    public function __construct(
        VariationHandler $variationHandler,
        RequestInterface $request,
        Factory $optionsFactory
    ) {
        $this->variationHandler = $variationHandler;
        $this->request = $request;
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * Initialize data for configurable product
     *
     * @param Helper $subject
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(Helper $subject, ProductInterface $product)
    {
        $attributes = $this->request->getParam('attributes');
        $productData = $this->request->getPost('product', []);

        if ($product->getTypeId() !== ConfigurableProduct::TYPE_CODE || empty($attributes)) {
            return $product;
        }

        $setId = $this->request->getPost('new-variations-attribute-set-id');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $extensionAttributes = $product->getExtensionAttributes();

        $product->setNewVariationsAttributeSetId($setId);

        $configurableOptions = [];
        if (!empty($productData['configurable_attributes_data'])) {
            $configurableOptions = $this->optionsFactory->create(
                (array) $productData['configurable_attributes_data']
            );
        }

        $extensionAttributes->setConfigurableProductOptions($configurableOptions);

        $this->setLinkedProducts($product, $extensionAttributes);
        $product->setCanSaveConfigurableAttributes(
            (bool) $this->request->getPost('affect_configurable_product_attributes')
        );

        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * Relate simple products to configurable
     *
     * @param ProductInterface $product
     * @param ProductExtensionInterface $extensionAttributes
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setLinkedProducts(ProductInterface $product, ProductExtensionInterface $extensionAttributes)
    {
        $associatedProductIds = $this->request->getPost('associated_product_ids', []);
        $variationsMatrix = $this->getVariationMatrix();
        if (!empty($variationsMatrix)) {
            $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
            $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
        }
        $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));
    }

    /**
     * Get variation-matrix from request
     *
     * @return array
     */
    protected function getVariationMatrix()
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix', []);
        foreach ($configurableMatrix as $item) {
            if ($item['newProduct']) {
                $result[$item['variationKey']] = [
                    'status' => isset($item['status']) ? $item['status'] : '',
                    'sku' => isset($item['sku']) ? $item['sku'] : '',
                    'name' => isset($item['name']) ? $item['name'] : '',
                    'price' => isset($item['price']) ? $item['price'] : '',
                    'configurable_attribute' => isset($item['configurable_attribute'])
                        ? $item['configurable_attribute'] : '',
                    'weight' => isset($item['weight']) ? $item['weight'] : '',
                    'media_gallery' => isset($item['media_gallery']) ? $item['media_gallery'] : '',
                    'swatch_image' => isset($item['swatch_image']) ? $item['swatch_image'] : '',
                    'small_image' => isset($item['small_image']) ? $item['small_image'] : '',
                    'thumbnail' => isset($item['thumbnail']) ? $item['thumbnail'] : '',
                    'image' => isset($item['image']) ? $item['image'] : '',
                ];

                if (isset($item['qty'])) {
                    $result[$item['variationKey']]['quantity_and_stock_status']['qty'] = $item['qty'];
                }
            }
        }

        return $result;
    }
}
