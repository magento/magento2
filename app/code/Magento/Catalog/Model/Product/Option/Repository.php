<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option
     */
    protected $optionResource;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Resource\Product\Option $optionResource
     * @param Converter $converter
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Resource\Product\Option $optionResource,
        \Magento\Catalog\Model\Product\Option\Converter $converter
    ) {
        $this->productRepository = $productRepository;
        $this->optionResource = $optionResource;
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $product = $this->productRepository->get($productSku, true);
        return $product->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function get($productSku, $optionId)
    {
        $product = $this->productRepository->get($productSku);
        $option = $product->getOptionById($optionId);
        if (is_null($option)) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $entity)
    {
        $this->optionResource->delete($entity);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
    {
        $productSku = $option->getProductSku();
        $product = $this->productRepository->get($productSku, true);
        $optionData = $this->converter->toArray($option);
        if ($option->getOptionId()) {
            if (!$product->getOptionById($option->getOptionId())) {
                throw new NoSuchEntityException();
            }
            $originalValues = $product->getOptionById($option->getOptionId())->getValues();
            if (!empty($optionData['values'])) {
                $optionData['values'] = $this->markRemovedValues($optionData['values'], $originalValues);
            }
        }

        unset($optionData['product_sku']);

        $product->setProductOptions([$optionData]);
        $existingOptions = $product->getOptions();
        try {
            $this->productRepository->save($product, true);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save product option');
        }

        $product = $this->productRepository->get($productSku, true);
        if (!$option->getOptionId()) {
            $currentOptions = $product->getOptions();

            $newID = array_diff(array_keys($currentOptions), array_keys($existingOptions));
            if (empty($newID)) {
                throw new CouldNotSaveException('Could not save product option');
            }
            $newID = current($newID);
        } else {
            $newID = $option->getOptionId();
        }
        $option = $this->get($productSku, $newID);
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier($productSku, $optionId)
    {
        $product = $this->productRepository->get($productSku, true);
        $options = $product->getOptions();
        $option = $product->getOptionById($optionId);
        if (is_null($option)) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }
        unset($options[$optionId]);
        try {
            $this->delete($option);
            if (empty($options)) {
                $this->productRepository->save($product);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not remove custom option');
        }
        return true;
    }

    /**
     * Mark original values for removal if they are absent among new values
     *
     * @param $newValues array
     * @param $originalValues \Magento\Catalog\Model\Product\Option\Value[]
     * @return array
     */
    protected function markRemovedValues($newValues, $originalValues)
    {
        $existingValuesIds = [];

        foreach ($newValues as $newValue) {
            if (array_key_exists('option_type_id', $newValue)) {
                $existingValuesIds[] = $newValue['option_type_id'];
            }
        }
        /** @var $originalValue \Magento\Catalog\Model\Product\Option\Value */
        foreach ($originalValues as $originalValue) {
            if (!in_array($originalValue->getData('option_type_id'), $existingValuesIds)) {
                $originalValue->setData('is_delete', 1);
                $newValues[] = $originalValue->getData();
            }
        }

        return $newValues;
    }
}
