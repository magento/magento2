<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagement implements \Magento\Bundle\Api\ProductLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\BundleFactory
     */
    protected $bundleFactory;

    /**
     * @var SelectionFactory
     */
    protected $bundleSelection;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory
     */
    protected $optionCollection;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory
     * @param \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\SelectionFactory $bundleSelection
     * @param \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory $optionCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory,
        \Magento\Bundle\Model\SelectionFactory $bundleSelection,
        \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory $optionCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
        $this->bundleFactory = $bundleFactory;
        $this->bundleSelection = $bundleSelection;
        $this->optionCollection = $optionCollection;
        $this->storeManager = $storeManager;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($productSku, $optionId = null)
    {
        $product = $this->productRepository->get($productSku);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('Only implemented for bundle product'));
        }

        $childrenList = [];
        foreach ($this->getOptions($product) as $option) {
            if (!$option->getSelections() || ($optionId !== null && $option->getOptionId() != $optionId)) {
                continue;
            }
            /** @var \Magento\Catalog\Model\Product $selection */
            foreach ($option->getSelections() as $selection) {
                $childrenList[] = $this->buildLink($selection, $product);
            }
        }
        return $childrenList;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildByProductSku($sku, $optionId, \Magento\Bundle\Api\Data\LinkInterface $linkedProduct)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        return $this->addChild($product, $optionId, $linkedProduct);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveChild(
        $sku,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    ) {
        $product = $this->productRepository->get($sku);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('Product with specified sku: "%1" is not a bundle product', [$product->getSku()])
            );
        }

        /** @var \Magento\Catalog\Model\Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException(__('Bundle product could not contain another composite product'));
        }

        if (!$linkedProduct->getId()) {
            throw new InputException(__('Id field of product link is required'));
        }

        /** @var \Magento\Bundle\Model\Selection $selectionModel */
        $selectionModel = $this->bundleSelection->create();
        $selectionModel->load($linkedProduct->getId());
        if (!$selectionModel->getId()) {
            throw new InputException(__('Can not find product link with id "%1"', [$linkedProduct->getId()]));
        }

        $selectionModel = $this->mapProductLinkToSelectionModel(
            $selectionModel,
            $linkedProduct,
            $linkProductModel->getId(),
            $product->getId()
        );

        try {
            $selectionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return true;
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selectionModel
     * @param \Magento\Bundle\Api\Data\LinkInterface $productLink
     * @param string $linkedProductId
     * @param string $parentProductId
     * @return \Magento\Bundle\Model\Selection
     */
    protected function mapProductLinkToSelectionModel(
        \Magento\Bundle\Model\Selection $selectionModel,
        \Magento\Bundle\Api\Data\LinkInterface $productLink,
        $linkedProductId,
        $parentProductId
    ) {
        $selectionModel->setProductId($linkedProductId);
        $selectionModel->setParentProductId($parentProductId);
        if (($productLink->getOptionId() !== null)) {
            $selectionModel->setOptionId($productLink->getOptionId());
        }
        if ($productLink->getPosition() !== null) {
            $selectionModel->setPosition($productLink->getPosition());
        }
        if ($productLink->getQty() !== null) {
            $selectionModel->setSelectionQty($productLink->getQty());
        }
        if ($productLink->getPriceType() !== null) {
            $selectionModel->setSelectionPriceType($productLink->getPriceType());
        }
        if ($productLink->getPrice() !== null) {
            $selectionModel->setSelectionPriceValue($productLink->getPrice());
        }
        if ($productLink->getCanChangeQuantity() !== null) {
            $selectionModel->setSelectionCanChangeQty($productLink->getCanChangeQuantity());
        }
        if ($productLink->getIsDefault() !== null) {
            $selectionModel->setIsDefault($productLink->getIsDefault());
        }

        return $selectionModel;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $optionId,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    ) {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('Product with specified sku: "%1" is not a bundle product', $product->getSku())
            );
        }

        $options = $this->optionCollection->create();
        $options->setIdFilter($optionId);
        $existingOption = $options->getFirstItem();

        if (!$existingOption->getId()) {
            throw new InputException(
                __(
                    'Product with specified sku: "%1" does not contain option: "%2"',
                    [$product->getSku(), $optionId]
                )
            );
        }

        /* @var $resource \Magento\Bundle\Model\ResourceModel\Bundle */
        $resource = $this->bundleFactory->create();
        $selections = $resource->getSelectionsData($product->getId());
        /** @var \Magento\Catalog\Model\Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException(__('Bundle product could not contain another composite product'));
        }
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] == $optionId &&
                    $selection['product_id'] == $linkProductModel->getId()) {
                    throw new CouldNotSaveException(
                        __(
                            'Child with specified sku: "%1" already assigned to product: "%2"',
                            [$linkedProduct->getSku(), $product->getSku()]
                        )
                    );
                }
            }
        }

        $selectionModel = $this->bundleSelection->create();
        $selectionModel = $this->mapProductLinkToSelectionModel(
            $selectionModel,
            $linkedProduct,
            $linkProductModel->getId(),
            $product->getId()
        );
        $selectionModel->setOptionId($optionId);

        try {
            $selectionModel->save();
            $resource->addProductRelations($product->getId(), [$linkProductModel->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return $selectionModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($sku, $optionId, $childSku)
    {
        $product = $this->productRepository->get($sku);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('Product with specified sku: %1 is not a bundle product', $sku));
        }

        $excludeSelectionIds = [];
        $usedProductIds = [];
        $removeSelectionIds = [];
        foreach ($this->getOptions($product) as $option) {
            /** @var \Magento\Bundle\Model\Selection $selection */
            foreach ($option->getSelections() as $selection) {
                if ((strcasecmp($selection->getSku(), $childSku) == 0) && ($selection->getOptionId() == $optionId)) {
                    $removeSelectionIds[] = $selection->getSelectionId();
                    continue;
                }
                $excludeSelectionIds[] = $selection->getSelectionId();
                $usedProductIds[] = $selection->getProductId();
            }
        }
        if (empty($removeSelectionIds)) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Requested bundle option product doesn\'t exist')
            );
        }
        /* @var $resource \Magento\Bundle\Model\ResourceModel\Bundle */
        $resource = $this->bundleFactory->create();
        $resource->dropAllUnneededSelections($product->getId(), $excludeSelectionIds);
        $resource->removeProductRelations($product->getId(), array_unique($usedProductIds));

        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $selection
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Api\Data\LinkInterface
     */
    private function buildLink(\Magento\Catalog\Model\Product $selection, \Magento\Catalog\Model\Product $product)
    {
        $selectionPriceType = $selectionPrice = null;

        /** @var \Magento\Bundle\Model\Selection $product */
        if ($product->getPriceType()) {
            $selectionPriceType = $selection->getSelectionPriceType();
            $selectionPrice = $selection->getSelectionPriceValue();
        }

        /** @var \Magento\Bundle\Api\Data\LinkInterface $link */
        $link = $this->linkFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $link,
            $selection->getData(),
            '\Magento\Bundle\Api\Data\LinkInterface'
        );
        $link->setIsDefault($selection->getIsDefault())
            ->setId($selection->getSelectionId())
            ->setQty($selection->getSelectionQty())
            ->setCanChangeQuantity($selection->getSelectionCanChangeQty())
            ->setPrice($selectionPrice)
            ->setPriceType($selectionPriceType);
        return $link;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    private function getOptions(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );

        $optionCollection = $productTypeInstance->getOptionsCollection($product);

        $selectionCollection = $productTypeInstance->getSelectionsCollection(
            $productTypeInstance->getOptionsIds($product),
            $product
        );

        $options = $optionCollection->appendSelections($selectionCollection);
        return $options;
    }
}
