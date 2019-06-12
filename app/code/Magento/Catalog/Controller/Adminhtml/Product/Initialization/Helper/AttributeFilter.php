<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

use \Magento\Catalog\Model\Product;

/**
 * Class provides functionality to check and filter data came with product form.
 *
 * The main goal is to avoid database population with empty(null) attribute values.
 */
class AttributeFilter
{
    /**
     * Method provides product data check and its further filtration.
     *
     * Filtration helps us to avoid unnecessary empty product data to be saved.
     * Empty data will be preserved only if user explicitly set it.
     *
     * @param Product $product
     * @param array $productData
     * @param array $useDefaults
     * @return array
     */
    public function prepareProductAttributes(Product $product, array $productData, array $useDefaults): array
<<<<<<< HEAD
    {
        $attributeList = $product->getAttributes();
        foreach ($productData as $attributeCode => $attributeValue) {
            if ($this->isAttributeShouldNotBeUpdated($product, $useDefaults, $attributeCode, $attributeValue)) {
                unset($productData[$attributeCode]);
            }

            if (isset($useDefaults[$attributeCode]) && $useDefaults[$attributeCode] === '1') {
                $productData = $this->prepareDefaultData($attributeList, $attributeCode, $productData);
                $productData = $this->prepareConfigData($product, $attributeCode, $productData);
            }
        }

        return $productData;
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @param array $productData
     * @return array
     */
    private function prepareConfigData(Product $product, $attributeCode, array $productData): array
    {
        // UI component sends value even if field is disabled, so 'Use Config Settings' must be reset to false
        if ($product->hasData('use_config_' . $attributeCode)) {
            $productData['use_config_' . $attributeCode] = false;
        }

        return $productData;
    }

    /**
     * @param array $attributeList
     * @param string $attributeCode
     * @param array $productData
     * @return array
     */
    private function prepareDefaultData(array $attributeList, $attributeCode, array $productData): array
    {
=======
    {
        $attributeList = $product->getAttributes();
        foreach ($productData as $attributeCode => $attributeValue) {
            if ($this->isAttributeShouldNotBeUpdated($product, $useDefaults, $attributeCode, $attributeValue)) {
                unset($productData[$attributeCode]);
            }

            if (isset($useDefaults[$attributeCode]) && $useDefaults[$attributeCode] === '1') {
                $productData = $this->prepareDefaultData($attributeList, $attributeCode, $productData);
                $productData = $this->prepareConfigData($product, $attributeCode, $productData);
            }
        }

        return $productData;
    }

    /**
     * Reset "Use Config Settings" to false in product data.
     *
     * @param Product $product
     * @param string $attributeCode
     * @param array $productData
     * @return array
     */
    private function prepareConfigData(Product $product, string $attributeCode, array $productData): array
    {
        // UI component sends value even if field is disabled, so 'Use Config Settings' must be reset to false
        if ($product->hasData('use_config_' . $attributeCode)) {
            $productData['use_config_' . $attributeCode] = false;
        }

        return $productData;
    }

    /**
     * Prepare default attribute data for product.
     *
     * @param array $attributeList
     * @param string $attributeCode
     * @param array $productData
     * @return array
     */
    private function prepareDefaultData(array $attributeList, string $attributeCode, array $productData): array
    {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        if (isset($attributeList[$attributeCode])) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $attributeList[$attributeCode];
            $attributeType = $attribute->getBackendType();
<<<<<<< HEAD
            // For non-numberic types set the attributeValue to 'false' to trigger their removal from the db
=======
            // For non-numeric types set the attributeValue to 'false' to trigger their removal from the db
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            if ($attributeType === 'varchar' || $attributeType === 'text' || $attributeType === 'datetime') {
                $attribute->setIsRequired(false);
                $productData[$attributeCode] = false;
            } else {
                $productData[$attributeCode] = null;
            }
        }

        return $productData;
    }

    /**
<<<<<<< HEAD
     * @param Product $product
     * @param $useDefaults
     * @param $attribute
     * @param $value
     * @return bool
     */
    private function isAttributeShouldNotBeUpdated(Product $product, $useDefaults, $attribute, $value): bool
=======
     * Check, whether attribute should not be updated.
     *
     * @param Product $product
     * @param array $useDefaults
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    private function isAttributeShouldNotBeUpdated(Product $product, array $useDefaults, $attribute, $value): bool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $considerUseDefaultsAttribute = !isset($useDefaults[$attribute]) || $useDefaults[$attribute] === '1';

        return ($value === '' && $considerUseDefaultsAttribute && !$product->getData($attribute));
    }
}
