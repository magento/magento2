<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception;
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
     * @var \Magento\ConfigurableProduct\Api\Data\OptionValueDataBuilder
     */
    protected $optionValueBuilder;

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
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueDataBuilder $optionValueBuilder
     * @param ConfigurableType $configurableType
     * @param Resource\Product\Type\Configurable\Attribute $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType\AttributeFactory $configurableAttributeFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\Data\OptionValueDataBuilder $optionValueBuilder,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory $configurableAttributeFactory
    ) {
        $this->productRepository = $productRepository;
        $this->optionValueBuilder = $optionValueBuilder;
        $this->configurableType = $configurableType;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);
        $collection = $this->getConfigurableAttributesCollection($product);
        $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), $optionId);
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configurableAttribute */
        $configurableAttribute = $collection->getFirstItem();
        if (!$configurableAttribute->getId()) {
            throw new NoSuchEntityException(sprintf('Requested option doesn\'t exist: %s', $optionId));
        }
        $prices = $configurableAttribute->getPrices();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $values[] = $this->optionValueBuilder
                    ->setValueIndex($price['value_index'])
                    ->setPricingValue($price['pricing_value'])
                    ->setIsPercent($price['is_percent'])
                    ->create();
            }
        }
        $configurableAttribute->setValues($values);
        return $configurableAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $options = [];
        $product = $this->getProduct($productSku);
        foreach ($this->getConfigurableAttributesCollection($product) as $option) {
            $values = [];
            $prices = $option->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $price) {
                    $values[] = $this->optionValueBuilder
                        ->setValueIndex($price['value_index'])
                        ->setPricingValue($price['pricing_value'])
                        ->setIsPercent($price['is_percent'])
                        ->create();
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
                sprintf('Cannot delete option with id: %s', $option->getId())
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);
        $attributeCollection = $this->configurableType->getConfigurableAttributeCollection($product);
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $option */
        $option = $attributeCollection->getItemById($optionId);
        if ($option === null) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        return $this->delete($option);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save($productSku, \Magento\ConfigurableProduct\Api\Data\OptionInterface $option)
    {
        /** @var $configurableAttribute \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute */
        $configurableAttribute = $this->configurableAttributeFactory->create();
        if ($option->getId()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($productSku);
            $configurableAttribute->load($option->getId());
            if (!$configurableAttribute->getId() || $configurableAttribute->getProductId() != $product->getId()) {
                throw new NoSuchEntityException(
                    'Option with id "%option_id" not found',
                    ['option_id' => $option->getId()]
                );
            }
            $configurableAttribute->addData($option->getData());
            $configurableAttribute->setValues(
                !is_null($option->getValues()) ? $option->getValues() : $configurableAttribute->getPrices()
            );

            try {
                $configurableAttribute->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    'Could not update option with id "%option_id"',
                    ['option_id' => $option->getId()]
                );
            }
        } else {
            $this->validateNewOptionData($option);
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($productSku);
            $allowedTypes = [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, ConfigurableType::TYPE_CODE];
            if (!in_array($product->getTypeId(), $allowedTypes)) {
                throw new \InvalidArgumentException('Incompatible product type');
            }

            $eavAttribute = $this->productAttributeRepository->get($option->getAttributeId());
            $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
            if ($configurableAttribute->getId()) {
                throw new CouldNotSaveException('Product already has this option');
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
                throw new CouldNotSaveException('An error occurred while saving option');
            }

            $configurableAttribute = $this->configurableAttributeFactory->create();
            $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
        }
        if (!$configurableAttribute->getId()) {
            throw new CouldNotSaveException('An error occurred while saving option');
        }
        return $configurableAttribute->getId();
    }

    /**
     * Retrieve product instance by sku
     *
     * @param string $productSku
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Webapi\Exception
     */
    private function getProduct($productSku)
    {
        $product = $this->productRepository->get($productSku);
        if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE !== $product->getTypeId()) {
            throw new Exception(
                sprintf('Only implemented for configurable product: %s', $productSku),
                Exception::HTTP_FORBIDDEN
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
            $inputException->addError('Option attribute ID is not specified.');
        }
        if (!$option->getType()) {
            $inputException->addError('Option type is not specified.');
        }
        if (!$option->getLabel()) {
            $inputException->addError('Option label is not specified.');
        }
        if (!$option->getValues()) {
            $inputException->addError('Option values are not specified.');
        } else {
            foreach ($option->getValues() as $optionValue) {
                if (!$optionValue->getValueIndex()) {
                    $inputException->addError('Value index is not specified for an option.');
                }
                if (null === $optionValue->getPricingValue()) {
                    $inputException->addError('Price is not specified for an option.');
                }
                if (null === $optionValue->getIsPercent()) {
                    $inputException->addError('Percent/absolute is not specified for an option.');
                }
            }
        }
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
    }
}
