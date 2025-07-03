<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Configurable product link management.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagement implements LinkManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private ProductInterfaceFactory $productFactory;

    /**
     * @var Configurable
     */
    private Configurable $configurableType;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var Factory;
     */
    private Factory $optionsFactory;

    /**
     * @var AttributeFactory
     */
    private AttributeFactory $attributeFactory;

    /**
     * @var ProductRepository|mixed
     */
    private ProductRepository $mediaGallery;

    /**
     * @var ProductAttributeMediaGalleryEntryInterfaceFactory|mixed
     */
    private ProductAttributeMediaGalleryEntryInterfaceFactory $myModelFactory;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productFactory
     * @param Configurable $configurableType
     * @param DataObjectHelper $dataObjectHelper
     * @param AttributeFactory|null $attributeFactory
     * @param ProductRepository|null $mediaGalleryProcessor
     * @param ProductAttributeMediaGalleryEntryInterfaceFactory|null $myModelFactory
     * @param Factory|null $optionsFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        Configurable $configurableType,
        DataObjectHelper $dataObjectHelper,
        ?AttributeFactory $attributeFactory = null,
        ?ProductRepository $mediaGalleryProcessor = null,
        ?ProductAttributeMediaGalleryEntryInterfaceFactory $myModelFactory = null,
        ?Factory $optionsFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->attributeFactory = $attributeFactory ?: ObjectManager::getInstance()
            ->get(AttributeFactory::class);
        $this->mediaGallery = $mediaGalleryProcessor ?: ObjectManager::getInstance()
            ->get(ProductRepository::class);
        $this->myModelFactory = $myModelFactory ?: ObjectManager::getInstance()
            ->get(ProductAttributeMediaGalleryEntryInterfaceFactory::class);
        $this->optionsFactory = $optionsFactory ?: ObjectManager::getInstance()
            ->get(Factory::class);
    }

    /**
     * @inheritdoc
     */
    public function getChildren($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        if ($product->getTypeId() != Product\Type\Configurable::TYPE_CODE) {
            return [];
        }
        /** @var Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);
        $childrenList = [];
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];
            foreach ($child->getAttributes() as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                if (null !== $value) {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();
            $productDataObject = $this->productFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productDataObject->setMediaGalleryEntries($child->getMediaGalleryEntries()),
                $attributes,
                ProductInterface::class
            );
            $childrenList[] = $productDataObject;
        }
        return $childrenList;
    }

    /**
     * Get media entries
     *
     * @param array $images
     * @return array
     * @deprecated This approach is designed only for images
     * @see ProductInterface::getMediaGalleryEntries
     */
    public function getMediaEntries(array $images): array
    {
        $media = $this->myModelFactory->create();
        $mediaGalleryEntries=[];
        foreach ($images as $image) {
            $media->setId($image["value_id"]);
            $media->setMediaType($image["media_type"]);
            $media->setLabel($image["label"]);
            $media->setPosition($image["position"]);
            $media->setDisabled($image["disabled"]);
            $media->setFile($image["file"]);
            $mediaGalleryEntries[]=$media->getData();
        }
        return $mediaGalleryEntries;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function addChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku, true);
        $child = $this->productRepository->get($childSku);

        $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
        if (in_array($child->getId(), $childrenIds)) {
            throw new StateException(__('The product is already attached.'));
        }

        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        if (empty($configurableProductOptions)) {
            throw new StateException(__("The parent product doesn't have configurable product options."));
        }

        $attributeData = [];
        foreach ($configurableProductOptions as $configurableProductOption) {
            $attributeCode = $configurableProductOption->getProductAttribute()->getAttributeCode();
            if (!$child->getData($attributeCode)) {
                throw new StateException(
                    __(
                        'The child product doesn\'t have the "%1" attribute value. Verify the value and try again.',
                        $attributeCode
                    )
                );
            }
            $attributeData[$configurableProductOption->getAttributeId()] = [
                'position' => $configurableProductOption->getPosition()
            ];
        }
        $configurableOptionData = $this->getConfigurableAttributesData($attributeData);

        /** @var Factory $optionFactory */
        $optionFactory = $this->optionsFactory;
        $options = $optionFactory->create($configurableOptionData);
        $childrenIds[] = $child->getId();
        $product->getExtensionAttributes()->setConfigurableProductOptions($options);
        $product->getExtensionAttributes()->setConfigurableProductLinks($childrenIds);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function removeChild($sku, $childSku)
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() != Product\Type\Configurable::TYPE_CODE) {
            throw new InputException(
                __('The product with the "%1" SKU isn\'t a configurable product.', $sku)
            );
        }

        $options = $product->getTypeInstance()->getUsedProducts($product);
        $ids = [];
        foreach ($options as $option) {
            if ($option->getSku() == $childSku) {
                continue;
            }
            $ids[] = $option->getId();
        }
        if (count($options) == count($ids)) {
            throw new NoSuchEntityException(
                __("The option that was requested doesn't exist. Verify the entity and try again.")
            );
        }
        $product->getExtensionAttributes()->setConfigurableProductLinks($ids);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * Get Configurable Attribute Data
     *
     * @param int[] $attributeData
     * @return array
     */
    private function getConfigurableAttributesData($attributeData)
    {
        $configurableAttributesData = [];
        $attributeValues = [];
        $attributes = $this->attributeFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', array_keys($attributeData))
            ->getItems();
        foreach ($attributes as $attribute) {
            foreach ($attribute->getOptions() as $option) {
                if ($option->getValue()) {
                    $attributeValues[] = [
                        'label' => $option->getLabel(),
                        'attribute_id' => $attribute->getId(),
                        'value_index' => $option->getValue(),
                    ];
                }
            }
            $configurableAttributesData[] =
                [
                    'attribute_id' => $attribute->getId(),
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'position' => $attributeData[$attribute->getId()]['position'],
                    'values' => $attributeValues,
                ];
        }

        return $configurableAttributesData;
    }
}
