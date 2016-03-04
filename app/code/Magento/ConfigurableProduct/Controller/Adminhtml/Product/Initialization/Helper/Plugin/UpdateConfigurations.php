<?php
/**
 * Product initialzation helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class UpdateConfigurations
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface  */
    protected $productRepository;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var \Magento\ConfigurableProduct\Model\Product\VariationHandler */
    protected $variationHandler;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->variationHandler = $variationHandler;
    }

    /**
     * Update data for configurable product configurations
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $configurableProduct
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $configurableProduct
    ) {
        //$configurations = $this->request->getParam('configurations', []);
        $configurations = $this->getConfigurations();
        $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
        foreach ($configurations as $productId => $productData) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId, false, $this->request->getParam('store', 0));
            $productData = $this->variationHandler->processMediaGallery($product, $productData);
            $product->addData($productData);
            if ($product->hasDataChanges()) {
                $product->save();
            }
        }
        return $configurableProduct;
    }

    /**
     * Get configurations from request
     *
     * @return array
     */
    protected function getConfigurations()
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix', []);
        foreach ($configurableMatrix as $item) {
            if (!$item['newProduct']) {
                $result[$item['id']] = [
                    'status' => $this->getItemValue($item, 'status'),
                    'sku' => $this->getItemValue($item, 'sku'),
                    'name' => $this->getItemValue($item, 'name'),
                    'price' => $this->getItemValue($item, 'price'),
                    'configurable_attribute' => $this->getItemValue($item, 'configurable_attribute'),
                    'weight' => $this->getItemValue($item, 'weight'),
                    'media_gallery' => $this->getItemValue($item, 'media_gallery'),
                    'swatch_image' => $this->getItemValue($item, 'swatch_image'),
                    'small_image' => $this->getItemValue($item, 'small_image'),
                    'thumbnail' => $this->getItemValue($item, 'thumbnail'),
                    'image' => $this->getItemValue($item, 'image')
                ];

                if (isset($item['qty'])) {
                    $result[$item['id']]['quantity_and_stock_status']['qty'] = $item['qty'];
                }
            }
        }

        return $result;
    }

    /**
     * Get item value
     *
     * @param array $item
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    private function getItemValue(array $item, $key, $defaultValue = '')
    {
        return isset($item[$key]) ? $item[$key] : $defaultValue;
    }
}
