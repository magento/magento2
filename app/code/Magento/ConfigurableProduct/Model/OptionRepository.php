<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
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
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute
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
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurableTypeResource;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory
     * @param ConfigurableType $configurableType
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType\AttributeFactory $configurableAttributeFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory $configurableAttributeFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource
    ) {
        $this->productRepository = $productRepository;
        $this->optionValueFactory = $optionValueFactory;
        $this->configurableType = $configurableType;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
        $this->configurableTypeResource = $configurableTypeResource;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $id)
    {
        $product = $this->getProduct($sku);

        $extensionAttribute = $product->getExtensionAttributes();
        if ($extensionAttribute !== null) {
            $options = $extensionAttribute->getConfigurableProductOptions();
            foreach ($options as $option) {
                if ($option->getId() == $id) {
                    return $option;
                }
            }
        }
        throw new NoSuchEntityException(__('Requested option doesn\'t exist: %1', $id));
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $options = [];
        $product = $this->getProduct($sku);

        $extensionAttribute = $product->getExtensionAttributes();
        if ($extensionAttribute !== null) {
            $options = $extensionAttribute->getConfigurableProductOptions();
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(OptionInterface $option)
    {
        $product = $this->getProductById($option->getProductId());
        try {
            $this->configurableTypeResource->saveProducts($product, []);
            $this->configurableType->resetConfigurableAttributes($product);
        } catch (\Exception $exception) {
            throw new StateException(
                __('Cannot delete variations from product: %1', $option->getProductId())
            );
        }
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
    public function save($sku, OptionInterface $option)
    {
        /** @var $configurableAttribute \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute */
        $configurableAttribute = $this->configurableAttributeFactory->create();
        if ($option->getId()) {
            /** @var Product $product */
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
                $option->getValues() !== null ? $option->getValues() : $configurableAttribute->getOptions()
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
            /** @var Product $product */
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
                throw new CouldNotSaveException(__('Something went wrong while saving option.'));
            }

            $configurableAttribute = $this->configurableAttributeFactory->create();
            $configurableAttribute->loadByProductAndAttribute($product, $eavAttribute);
        }
        if (!$configurableAttribute->getId()) {
            throw new CouldNotSaveException(__('Something went wrong while saving option.'));
        }
        return $configurableAttribute->getId();
    }

    /**
     * Retrieve product instance by sku
     *
     * @param string $sku
     * @return ProductInterface
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
     * Retrieve product instance by id
     *
     * @param int $id
     * @return ProductInterface
     * @throws InputException
     */
    private function getProductById($id)
    {
        $product = $this->productRepository->getById($id);
        if (\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE !== $product->getTypeId()) {
            throw new InputException(
                __('Only implemented for configurable product: %1', $id)
            );
        }
        return $product;
    }

    /**
     * Ensure that all necessary data is available for a new option creation.
     *
     * @param OptionInterface $option
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateNewOptionData(OptionInterface $option)
    {
        $inputException = new InputException();
        if (!$option->getAttributeId()) {
            $inputException->addError(__('Option attribute ID is not specified.'));
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
            }
        }
        if ($inputException->wasErrorAdded()) {
            throw $inputException;
        }
    }
}
