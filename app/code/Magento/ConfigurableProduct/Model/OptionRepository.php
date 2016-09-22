<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\EntityManager\MetadataPool;
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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Loader
     */
    private $optionLoader;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory
     * @param ConfigurableType $configurableType
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType\AttributeFactory $configurableAttributeFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource
     * @param Loader $optionLoader
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory $optionValueFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute $optionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory $configurableAttributeFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableTypeResource,
        Loader $optionLoader
    ) {
        $this->productRepository = $productRepository;
        $this->optionValueFactory = $optionValueFactory;
        $this->configurableType = $configurableType;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->configurableAttributeFactory = $configurableAttributeFactory;
        $this->configurableTypeResource = $configurableTypeResource;
        $this->optionLoader = $optionLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $id)
    {
        $product = $this->getProduct($sku);

        $options = $this->optionLoader->load($product);
        foreach ($options as $option) {
            if ($option->getId() == $id) {
                return $option;
            }
        }

        throw new NoSuchEntityException(__('Requested option doesn\'t exist: %1', $id));
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku)
    {
        $product = $this->getProduct($sku);

        return (array) $this->optionLoader->load($product);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(OptionInterface $option)
    {
        $entityId = $this->configurableTypeResource->getEntityIdByAttribute($option);
        $product = $this->getProductById($entityId);

        try {
            $this->configurableTypeResource->saveProducts($product, []);
            $this->configurableType->resetConfigurableAttributes($product);
        } catch (\Exception $exception) {
            throw new StateException(
                __('Cannot delete variations from product: %1', $entityId)
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
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        if ($option->getId()) {
            /** @var Product $product */
            $product = $this->getProduct($sku);
            $data = $option->getData();
            $option->load($option->getId());
            $option->setData(array_replace_recursive($option->getData(), $data));
            if (!$option->getId() || $option->getProductId() != $product->getData($metadata->getLinkField())) {
                throw new NoSuchEntityException(
                    __(
                        'Option with id "%1" not found',
                        $option->getId()
                    )
                );
            }
        } else {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
            $this->validateNewOptionData($option);
            $allowedTypes = [ProductType::TYPE_SIMPLE, ProductType::TYPE_VIRTUAL, ConfigurableType::TYPE_CODE];
            if (!in_array($product->getTypeId(), $allowedTypes)) {
                throw new \InvalidArgumentException('Incompatible product type');
            }
            $option->setProductId($product->getData($metadata->getLinkField()));
        }

        try {
            $option->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Something went wrong while saving option.'));
        }

        if (!$option->getId()) {
            throw new CouldNotSaveException(__('Something went wrong while saving option.'));
        }
        return $option->getId();
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

    /**
     * Get MetadataPool instance
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
