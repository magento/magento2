<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Bundle;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Bundle\Model\ResourceModel\Option\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class used to manage bundle products links.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagement implements ProductLinkManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var BundleFactory
     */
    protected $bundleFactory;

    /**
     * @var SelectionFactory
     */
    protected $bundleSelection;

    /**
     * @var CollectionFactory
     */
    protected $optionCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param LinkInterfaceFactory $linkFactory
     * @param SelectionFactory $bundleSelection
     * @param BundleFactory $bundleFactory
     * @param CollectionFactory $optionCollection
     * @param StoreManagerInterface $storeManager
     * @param DataObjectHelper $dataObjectHelper
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        LinkInterfaceFactory $linkFactory,
        SelectionFactory $bundleSelection,
        BundleFactory $bundleFactory,
        CollectionFactory $optionCollection,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper,
        MetadataPool $metadataPool
    ) {
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
        $this->bundleFactory = $bundleFactory;
        $this->bundleSelection = $bundleSelection;
        $this->optionCollection = $optionCollection;
        $this->storeManager = $storeManager;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritDoc
     */
    public function getChildren($productSku, $optionId = null)
    {
        $product = $this->productRepository->get($productSku, true);
        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('This is implemented for bundle products only.'));
        }

        $childrenList = [];
        foreach ($this->getOptions($product) as $option) {
            if (!$option->getSelections() || ($optionId !== null && $option->getOptionId() != $optionId)) {
                continue;
            }
            /** @var Product $selection */
            foreach ($option->getSelections() as $selection) {
                $childrenList[] = $this->buildLink($selection, $product);
            }
        }
        return $childrenList;
    }

    /**
     * @inheritDoc
     */
    public function addChildByProductSku($sku, $optionId, LinkInterface $linkedProduct)
    {
        /** @var Product $product */
        $product = $this->productRepository->get($sku, true);
        return $this->addChild($product, $optionId, $linkedProduct);
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveChild(
        $sku,
        LinkInterface $linkedProduct
    ) {
        $product = $this->productRepository->get($sku, true);
        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('The product with the "%1" SKU isn\'t a bundle product.', [$product->getSku()])
            );
        }

        /** @var Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException(__('The bundle product can\'t contain another composite product.'));
        }

        if (!$linkedProduct->getId()) {
            throw new InputException(__('The product link needs an ID field entered. Enter and try again.'));
        }

        /** @var Selection $selectionModel */
        $selectionModel = $this->bundleSelection->create();
        $selectionModel->load($linkedProduct->getId());
        if (!$selectionModel->getId()) {
            throw new InputException(
                __(
                    'The product link with the "%1" ID field wasn\'t found. Verify the ID and try again.',
                    [$linkedProduct->getId()]
                )
            );
        }
        $selectionModel = $this->mapProductLinkToBundleSelectionModel(
            $selectionModel,
            $linkedProduct,
            $product,
            (int)$linkProductModel->getId()
        );

        try {
            $selectionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return true;
    }

    /**
     * Fill selection model with product link data
     *
     * @param Selection $selectionModel
     * @param LinkInterface $productLink
     * @param string $linkedProductId
     * @param string $parentProductId
     *
     * @return Selection
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated use mapProductLinkToBundleSelectionModel
     */
    protected function mapProductLinkToSelectionModel(
        Selection $selectionModel,
        LinkInterface $productLink,
        $linkedProductId,
        $parentProductId
    ) {
        $selectionModel->setProductId($linkedProductId);
        $selectionModel->setParentProductId($parentProductId);
        if ($productLink->getSelectionId() !== null) {
            $selectionModel->setSelectionId($productLink->getSelectionId());
        }
        if ($productLink->getOptionId() !== null) {
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
     * Fill selection model with product link data.
     *
     * @param Selection $selectionModel
     * @param LinkInterface $productLink
     * @param ProductInterface $parentProduct
     * @param int $linkedProductId
     * @param string $linkField
     * @return Selection
     * @throws NoSuchEntityException
     */
    private function mapProductLinkToBundleSelectionModel(
        Selection $selectionModel,
        LinkInterface $productLink,
        ProductInterface $parentProduct,
        int $linkedProductId
    ): Selection {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $selectionModel->setProductId($linkedProductId);
        $selectionModel->setParentProductId($parentProduct->getData($linkField));
        if ($productLink->getSelectionId() !== null) {
            $selectionModel->setSelectionId($productLink->getSelectionId());
        }
        if ($productLink->getOptionId() !== null) {
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
        $selectionModel->setWebsiteId((int)$this->storeManager->getStore($parentProduct->getStoreId())->getWebsiteId());

        return $selectionModel;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addChild(
        ProductInterface $product,
        $optionId,
        LinkInterface $linkedProduct
    ) {
        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('The product with the "%1" SKU isn\'t a bundle product.', $product->getSku())
            );
        }

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $options = $this->optionCollection->create();

        $options->setIdFilter($optionId);
        $options->setProductLinkFilter($product->getData($linkField));

        $existingOption = $options->getFirstItem();

        if (!$existingOption->getId()) {
            throw new InputException(
                __(
                    'Product with specified sku: "%1" does not contain option: "%2"',
                    [$product->getSku(), $optionId]
                )
            );
        }

        /* @var $resource Bundle */
        $resource = $this->bundleFactory->create();
        $selections = $resource->getSelectionsData($product->getData($linkField));
        /** @var Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException(__('The bundle product can\'t contain another composite product.'));
        }

        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] == $optionId &&
                    $selection['product_id'] == $linkProductModel->getEntityId() &&
                    $selection['parent_product_id'] == $product->getData($linkField)) {
                    if (!$product->getCopyFromView()) {
                        throw new CouldNotSaveException(
                            __(
                                'Child with specified sku: "%1" already assigned to product: "%2"',
                                [$linkedProduct->getSku(), $product->getSku()]
                            )
                        );
                    }

                    return $this->bundleSelection->create()->load($linkProductModel->getEntityId());
                }
            }
        }

        $selectionModel = $this->bundleSelection->create();
        $selectionModel = $this->mapProductLinkToBundleSelectionModel(
            $selectionModel,
            $linkedProduct,
            $product,
            (int)$linkProductModel->getEntityId()
        );

        $selectionModel->setOptionId($optionId);

        try {
            $selectionModel->save();
            $resource->addProductRelation($product->getData($linkField), $linkProductModel->getEntityId());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return (int)$selectionModel->getId();
    }

    /**
     * @inheritDoc
     */
    public function removeChild($sku, $optionId, $childSku)
    {
        $product = $this->productRepository->get($sku, true);

        if ($product->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('The product with the "%1" SKU isn\'t a bundle product.', $sku));
        }

        $excludeSelectionIds = [];
        $usedProductIds = [];
        $removeSelectionIds = [];
        foreach ($this->getOptions($product) as $option) {
            /** @var Selection $selection */
            foreach ($option->getSelections() as $selection) {
                if ((strcasecmp($selection->getSku(), $childSku) == 0) && ($selection->getOptionId() == $optionId)) {
                    $removeSelectionIds[] = $selection->getSelectionId();
                    $usedProductIds[] = $selection->getProductId();
                    continue;
                }
                $excludeSelectionIds[] = $selection->getSelectionId();
            }
        }
        if (empty($removeSelectionIds)) {
            throw new NoSuchEntityException(
                __("The bundle product doesn't exist. Review your request and try again.")
            );
        }
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        /* @var $resource Bundle */
        $resource = $this->bundleFactory->create();
        $resource->dropAllUnneededSelections($product->getData($linkField), $excludeSelectionIds);
        $resource->removeProductRelations($product->getData($linkField), array_unique($usedProductIds));

        return true;
    }

    /**
     * Build bundle link between two products
     *
     * @param Product $selection
     * @param Product $product
     *
     * @return LinkInterface
     */
    private function buildLink(Product $selection, Product $product)
    {
        $selectionPriceType = $selectionPrice = null;

        /** @var Selection $product */
        if ($product->getPriceType()) {
            $selectionPriceType = $selection->getSelectionPriceType();
            $selectionPrice = $selection->getSelectionPriceValue();
        }

        /** @var LinkInterface $link */
        $link = $this->linkFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $link,
            $selection->getData(),
            LinkInterface::class
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
     * Get bundle product options
     *
     * @param ProductInterface $product
     *
     * @return OptionInterface[]
     */
    private function getOptions(ProductInterface $product)
    {
        /** @var Type $productTypeInstance */
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

        return $optionCollection->appendSelections($selectionCollection, true);
    }
}
