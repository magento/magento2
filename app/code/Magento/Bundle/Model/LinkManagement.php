<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

class LinkManagement implements \Magento\Bundle\Api\ProductLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Bundle\Api\Data\LinkDataBuilder
     */
    protected $linkBuilder;

    /**
     * @var \Magento\Bundle\Model\Resource\BundleFactory
     */
    protected $bundleFactory;

    /**
     * @var SelectionFactory
     */
    protected $bundleSelection;

    /**
     * @var Resource\Option\CollectionFactory
     */
    protected $optionCollection;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder
     * @param \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\SelectionFactory $bundleSelection
     * @param \Magento\Bundle\Model\Resource\Option\CollectionFactory $optionCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder,
        \Magento\Bundle\Model\SelectionFactory $bundleSelection,
        \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\Resource\Option\CollectionFactory $optionCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->linkBuilder = $linkBuilder;
        $this->bundleFactory = $bundleFactory;
        $this->bundleSelection = $bundleSelection;
        $this->optionCollection = $optionCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($productId)
    {
        $product = $this->productRepository->get($productId);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new \Magento\Webapi\Exception(
                'Only implemented for bundle product',
                \Magento\Webapi\Exception::HTTP_FORBIDDEN
            );
        }

        $childrenList = [];
        foreach ($this->getOptions($product) as $option) {
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
    public function addChildByProductSku($productSku, $optionId, \Magento\Bundle\Api\Data\LinkInterface $linkedProduct)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);
        return $this->addChild($product, $optionId, $linkedProduct);
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
            throw new InputException('Product with specified sku: "%1" is not a bundle product', [$product->getSku()]);
        }

        $options = $this->optionCollection->create();
        $options->setProductIdFilter($product->getId())->joinValues($this->storeManager->getStore()->getId());
        $isNewOption = true;
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options as $option) {
            if ($option->getOptionId() == $optionId) {
                $isNewOption = false;
                break;
            }
        }

        if ($isNewOption) {
            throw new InputException(
                'Product with specified sku: "%1" does not contain option: "%2"',
                [$product->getSku(), $optionId]
            );
        }

        /* @var $resource \Magento\Bundle\Model\Resource\Bundle */
        $resource = $this->bundleFactory->create();
        $selections = $resource->getSelectionsData($product->getId());
        /** @var \Magento\Catalog\Model\Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->isComposite()) {
            throw new InputException('Bundle product could not contain another composite product');
        }
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] == $optionId &&
                    $selection['product_id'] == $linkProductModel->getId()) {
                    throw new CouldNotSaveException(
                        'Child with specified sku: "%1" already assigned to product: "%2"',
                        [$linkedProduct->getSku(), $product->getSku()]
                    );
                }
            }
        }

        $selectionModel = $this->bundleSelection->create();
        $selectionModel->setOptionId($optionId)
            ->setPosition($linkedProduct->getPosition())
            ->setSelectionQty($linkedProduct->getQty())
            ->setSelectionPriceType($linkedProduct->getPriceType())
            ->setSelectionPriceValue($linkedProduct->getPrice())
            ->setSelectionCanChangeQty($linkedProduct->getCanChangeQuantity())
            ->setProductId($linkProductModel->getId())
            ->setParentProductId($product->getId())
            ->setIsDefault($linkedProduct->getIsDefault())
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId());

        try {
            $selectionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save child: "%1"', [$e->getMessage()], $e);
        }

        return $selectionModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($productSku, $optionId, $childSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new \Magento\Webapi\Exception(
                sprintf('Product with specified sku: %s is not a bundle product', $productSku),
                \Magento\Webapi\Exception::HTTP_FORBIDDEN
            );
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
                'Requested bundle option product doesn\'t exist'
            );
        }
        /* @var $resource \Magento\Bundle\Model\Resource\Bundle */
        $resource = $this->bundleFactory->create();
        $resource->dropAllUnneededSelections($product->getId(), $excludeSelectionIds);
        $resource->saveProductRelations($product->getId(), array_unique($usedProductIds));

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

        return $this->linkBuilder->populateWithArray($selection->getData())
            ->setIsDefault($selection->getIsDefault())
            ->setQty($selection->getSelectionQty())
            ->setIsDefined($selection->getSelectionCanChangeQty())
            ->setPrice($selectionPrice)
            ->setPriceType($selectionPriceType)
            ->create();
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionTypeInterface[]
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
