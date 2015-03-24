<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepository implements \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory
     */
    protected $optionValueFactory;

    /**
     * @var Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @var Resource\Product\Type\Configurable\Attribute
     */
    protected $optionResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var ConfigurableType\AttributeFactory
     */
    protected $configurableAttributeFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory
     * @param ConfigurableType $configurableType
     * @param Resource\Product\Type\Configurable\Attribute $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType\AttributeFactory $configurableAttributeFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory $configurableAttributeFactory
    ) {
        $this->productRepository = $productRepository;
        $this->optionValueFactory = $optionValueFactory;
        $this->configurableType = $configurableType;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $id)
    {
        $product = $this->getProduct($sku);
        $collection = $this->getConfigurableAttributesCollection($product);
        $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), $id);
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configurableAttribute */
        $configurableAttribute = $collection->getFirstItem();
        if (!$configurableAttribute->getId()) {
            throw new NoSuchEntityException(__('Requested option doesn\'t exist: %1', $id));
        }
        $prices = $configurableAttribute->getPrices();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                $value = $this->optionValueFactory->create();
                $value->setValueIndex($price['value_index'])
                    ->setPricingValue($price['pricing_value'])
                    ->setIsPercent($price['is_percent']);
                $values[] = $value;
            }
        }
        $configurableAttribute->setValues($values);
        return $configurableAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $options = [];
        $product = $this->getProduct($sku);
        foreach ($this->getConfigurableAttributesCollection($product) as $option) {
            $values = [];
            $prices = $option->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $price) {
                    /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                    $value = $this->optionValueFactory->create();
                    $value->setValueIndex($price['value_index'])
                        ->setPricingValue($price['pricing_value'])
                        ->setIsPercent($price['is_percent']);
                    $values[] = $value;
                }
            }
            $option->setValues($values);
            $options[] = $option;
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\ConfigurableProduct\Api\Data\OptionInterface $option)
    {
        try {
            $this->optionResource->delete($option);
        } catch (\Exception $exception) {
            throw new StateException(
                __('Cannot delete option with id: %1', $option->getId())
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sku, $id)
    {
        $product = $this->getProduct($sku);
        $attributeCollection = $this->configurableType->getConfigurableAttributeCollection($product);
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $option */
        $option = $attributeCollection->getItemById($id);
        if ($option === null) {
            throw new NoSuchEntityException(__('Requested option doesn\'t exist'));
        }
        return $this->delete($option);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save($sku, \Magento\ConfigurableProduct\Api\Data\OptionInterface $option)
    {
        /** @var $configurableAttribute \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute */
        $configurableAttribute = $this->configurableAttributeFactory->create();
        if ($option->getId()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($sku);
            $configurableAttribute->load($option->getId());
            if (!$configurableAttribute->getId() || $configurableAttribute->getProductId() != $product->getId()) {
                throw new NoSuchEntityException(
                    __(
                        'Option with id "%1" not found',
                        $option->getId()
                    )
                );
            }
            $configurableAttribute->addData($option->getData());
            $configurableAttribute->setValues(
                $option->getValues() !== null ? $option->getValues() : $configurableAttribute->getPrices()
            );

            try {
                $configurableAttribute->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __(
                        'Could not update option with id "%1"',
                        $option->getId()
                    )
                );
            }
        } else {
            $this->validateNewOptionData($option);
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($sku);
            $allowedTypes = [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, ConfigurableType::TYPE_CODE];
            if (!in_array($product->getTypeId(), $allowedTypes)) {
                throw new \InvalidArgumentException('Incompatible product type');
            }

            $eavAttribute = $this->productAttributeRepository->get($option->getAttributeId());
            $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
            if ($configurableAttribute->getId()) {
                throw new CouldNotSaveException(__('Product already has this option'));
            }

            $configurableAttributesData = [
                'attribute_id' => $option->getAttributeId(),
                'position' => $option->getPosition(),
                'use_default' => $option->getIsUseDefault(),
                'label' => $option->getLabel(),
                'values' => $option->getValues()
            ];

            try {
                $product->setTypeId(ConfigurableType::TYPE_CODE);
                $product->setConfigurableAttributesData([$configurableAttributesData]);
                $product->setStoreId($this->storeManager->getStore(Store::ADMIN_CODE)->getId());
                $product->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('An error occurred while saving option'));
            }

            $configurableAttribute = $this->configurableAttributeFactory->create();
            $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
        }
        if (!$configurableAttribute->getId()) {
            throw new CouldNotSaveException(__('An error occurred while saving option'));
        }
        return $configurableAttribute->getId();
    }

    /**
     * Retrieve product instance by sku
     *
     * @param string $sku
     * @return \Magento\Catalog\Model\Product
     * @throws InputException
     */
    private function getProduct($sku)
    {
        $product = $this->productRepository->get($sku);
        if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE !== $product->getTypeId()) {
            throw new InputException(
                __('Only implemented for configurable product: %1', $sku)
            );
        }
        return $product;
    }

    /**
     * Retrieve configurable attribute collection through product object
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection
     */
    private function getConfigurableAttributesCollection(\Magento\Catalog\Model\Product $product)
    {
        return $this->configurableType->getConfigurableAttributeCollection($product);
    }

    /**
     * Ensure that all necessary data is available for a new option creation.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface $option
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateNewOptionData(\Magento\ConfigurableProduct\Api\Data\OptionInterface $option)
    {
        $inputException = new InputException();
        if (!$option->getAttributeId()) {
            $inputException->addError(__('Option attribute ID is not specified.'));
        }
        if (!$option->getType()) {
            $inputException->addError(__('Option type is not specified.'));
        }
        if (!$option->getLabel()) {
            $inputException->addError(__('Option label is not specified.'));
        }
        if (!$option->getValues()) {
            $inputException->addError(__('Option values are not specified.'));
        } else {
            foreach ($option->getValues() as $optionValue) {
                if (!$optionValue->getValueIndex()) {
                    $inputException->addError(__('Value index is not specified for an option.'));
                }
                if (null === $optionValue->getPricingValue()) {
                    $inputException->addError(__('Price is not specified for an option.'));
                }
                if (null === $optionValue->getIsPercent()) {
                    $inputException->addError(__('Percent/absolute is not specified for an option.'));
                }
            }
        }
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
    }
}
