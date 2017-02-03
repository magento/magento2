<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Validator;

use Closure;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Json\Helper\Data;

/**
 * Configurable product validation
 */
class Plugin
{
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param Manager $eventManager
     * @param ProductFactory $productFactory
     * @param Data $jsonHelper
     */
    public function __construct(
        Manager $eventManager,
        ProductFactory $productFactory,
        Data $jsonHelper
    ) {
        $this->eventManager = $eventManager;
        $this->productFactory = $productFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Validate product data
     *
     * @param Product\Validator $subject
     * @param Closure $proceed
     * @param Product $product
     * @param RequestInterface $request
     * @param \Magento\Framework\DataObject $response
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Catalog\Model\Product\Validator $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\DataObject $response
    ) {
        if ($request->has('attributes')) {
            $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        }
        $result = $proceed($product, $request, $response);
        $variationProducts = $request->getPost('configurable-matrix-serialized', '[]');
        $variationProducts = json_decode($variationProducts, true);
        if ($variationProducts) {
            $validationResult = $this->_validateProductVariations($product, $variationProducts, $request);
            if (!empty($validationResult)) {
                $response->setError(
                    true
                )->setMessage(
                    __('Some product variations fields are not valid.')
                )->setAttributes(
                    $validationResult
                );
            }
        }
        return $result;
    }

    /**
     * Product variations attributes validation
     *
     * @param Product $parentProduct
     * @param array $products
     * @param RequestInterface $request
     * @return array
     */
    protected function _validateProductVariations(Product $parentProduct, array $products, RequestInterface $request)
    {
        $this->eventManager->dispatch(
            'catalog_product_validate_variations_before',
            ['product' => $parentProduct, 'variations' => $products]
        );
        $validationResult = [];
        foreach ($products as $productData) {
            $product = $this->productFactory->create();
            $product->setData('_edit_mode', true);
            $storeId = $request->getParam('store');
            if ($storeId) {
                $product->setStoreId($storeId);
            }
            $product->setAttributeSetId($parentProduct->getAttributeSetId());
            $product->addData($this->getRequiredDataFromProduct($parentProduct));
            $product->addData($productData);
            $product->setCollectExceptionMessages(true);
            $configurableAttribute = [];
            $encodedData = $productData['configurable_attribute'];
            if ($encodedData) {
                $configurableAttribute = $this->jsonHelper->jsonDecode($encodedData);
            }
            $configurableAttribute = implode('-', $configurableAttribute);

            $errorAttributes = $product->validate();
            if (is_array($errorAttributes)) {
                foreach ($errorAttributes as $attributeCode => $result) {
                    if (is_string($result)) {
                        $key = 'variations-matrix-' . $configurableAttribute . '-' . $attributeCode;
                        $validationResult[$key] = $result;
                    }
                }
            }
        }
        return $validationResult;
    }

    /**
     * @param Product $product
     * @return array
     */
    protected function getRequiredDataFromProduct(Product $product)
    {
        $parentProductData = [];
        foreach ($product->getAttributes() as $attribute) {
            if ($attribute->getIsUserDefined() && $attribute->getIsRequired()) {
                $parentProductData[$attribute->getAttributeCode()] = $product->getData($attribute->getAttributeCode());
            }
        }
        return $parentProductData;
    }
}
