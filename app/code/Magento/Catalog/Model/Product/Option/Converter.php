<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Converter
 */
class Converter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Converter constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Convert option data to array
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return array
     */
    public function toArray(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
    {
        $optionData = $option->getData();
        $values = $option->getValues();
        $valuesData = [];
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $valuesData[$key] = $value->getData();
            }
        }
        $optionData['values'] = $valuesData;
        return $optionData;
    }

    /**
     * Process product options, creating new options, updating and deleting existing options
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $newOptions
     * @return $this
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processOptions(\Magento\Catalog\Api\Data\ProductInterface $product, $newOptions)
    {
        //existing options by option_id
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface[] $existingOptions */
        $existingOptions = $this->collectionFactory->create()->getProductOptions(
            $product->getEntityId(),
            $product->getStoreId()
        );
        if ($existingOptions === null) {
            $existingOptions = [];
        }

        $newOptionIds = [];
        foreach ($newOptions as $key => $option) {
            if (isset($option['option_id'])) {
                //updating existing option
                $optionId = $option['option_id'];
                if (!isset($existingOptions[$optionId])) {
                    throw new NoSuchEntityException(__('Product option with id %1 does not exist', $optionId));
                }
                $existingOption = $existingOptions[$optionId];
                $newOptionIds[] = $option['option_id'];
                if (isset($option['values'])) {
                    //updating option values
                    $optionValues = $option['values'];
                    $valueIds = [];
                    foreach ($optionValues as $optionValue) {
                        if (isset($optionValue['option_type_id'])) {
                            $valueIds[] = $optionValue['option_type_id'];
                        }
                    }
                    $originalValues = $existingOption->getValues();
                    foreach ($originalValues as $originalValue) {
                        if (!in_array($originalValue->getOptionTypeId(), $valueIds)) {
                            $originalValue->setData('is_delete', 1);
                            $optionValues[] = $originalValue->getData();
                        }
                    }
                    $newOptions[$key]['values'] = $optionValues;
                } else {
                    $existingOptionData = $this->toArray($existingOption);
                    if (isset($existingOptionData['values'])) {
                        $newOptions[$key]['values'] = $existingOptionData['values'];
                    }
                }
            }
        }

        $optionIdsToDelete = array_diff(array_keys($existingOptions), $newOptionIds);
        foreach ($optionIdsToDelete as $optionId) {
            $optionToDelete = $existingOptions[$optionId];
            $optionDataArray = $this->toArray($optionToDelete);
            $optionDataArray['is_delete'] = 1;
            $newOptions[] = $optionDataArray;
        }
        $product->setProductOptions($newOptions);
        return $this;
    }
}
