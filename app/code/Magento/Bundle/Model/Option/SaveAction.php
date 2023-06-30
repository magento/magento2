<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Option;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\ResourceModel\Option;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Encapsulates logic for saving a bundle option, including coalescing the parent product's data.
 */
class SaveAction
{
    /**
     * @var Option
     */
    private $optionResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var ProductLinkManagementInterface
     */
    private $linkManagement;

    /**
     * @param Option $optionResource
     * @param MetadataPool $metadataPool
     * @param Type $type
     * @param ProductLinkManagementInterface $linkManagement
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Option $optionResource,
        MetadataPool $metadataPool,
        Type $type,
        ProductLinkManagementInterface $linkManagement,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->optionResource = $optionResource;
        $this->metadataPool = $metadataPool;
        $this->type = $type;
        $this->linkManagement = $linkManagement;
    }

    /**
     * Manage the logic of saving a bundle option, including the coalescence of its parent product data.
     *
     * @param ProductInterface $bundleProduct
     * @param OptionInterface $option
     * @return OptionInterface
     * @throws CouldNotSaveException
     * @throws \Exception
     */
    public function save(ProductInterface $bundleProduct, OptionInterface $option)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $option->setStoreId($bundleProduct->getStoreId());
        $parentId = $bundleProduct->getData($metadata->getLinkField());
        $option->setParentId($parentId);

        $optionId = $option->getOptionId();
        $linksToAdd = [];
        $optionCollection = $this->type->getOptionsCollection($bundleProduct);
        $optionCollection->setIdFilter($option->getOptionId());
        $optionCollection->setProductLinkFilter($parentId);

        /** @var \Magento\Bundle\Model\Option $existingOption */
        $existingOption = $optionCollection->getFirstItem();
        if (!$optionId || $existingOption->getParentId() != $parentId) {
            //If option ID is empty or existing option's parent ID is different
            //we'd need a new ID for the option.
            $option->setOptionId(null);
            $option->setDefaultTitle($option->getTitle());
            if (is_array($option->getProductLinks())) {
                $linksToAdd = $option->getProductLinks();
            }
        } else {
            if (!$existingOption->getOptionId()) {
                throw new NoSuchEntityException(
                    __("The option that was requested doesn't exist. Verify the entity and try again.")
                );
            }

            $option->setData(array_merge($existingOption->getData(), $option->getData()));
            $this->updateOptionSelection($bundleProduct, $option);
        }

        try {
            $this->optionResource->save($option);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The option couldn't be saved."), $e);
        }

        /** @var LinkInterface $linkedProduct */
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($bundleProduct, $option->getOptionId(), $linkedProduct);
        }

        $bundleProduct->setIsRelationsChanged(true);

        return $option;
    }

    /**
     * Update option selections
     *
     * @param ProductInterface $product
     * @param OptionInterface $option
     * @return void
     */
    private function updateOptionSelection(ProductInterface $product, OptionInterface $option)
    {
        $optionId = $option->getOptionId();
        $existingLinks = $this->linkManagement->getChildren($product->getSku(), $optionId);
        $linksToAdd = [];
        $linksToUpdate = [];
        $linksToDelete = [];
        if (is_array($option->getProductLinks())) {
            $productLinks = $option->getProductLinks();
            foreach ($productLinks as $productLink) {
                if (!$productLink->getId() && !$productLink->getSelectionId()) {
                    $linksToAdd[] = $productLink;
                } else {
                    $linksToUpdate[] = $productLink;
                }
            }
            /** @var LinkInterface[] $linksToDelete */
            $linksToDelete = $this->compareLinks($existingLinks, $linksToUpdate);
        }
        foreach ($linksToUpdate as $linkedProduct) {
            $this->linkManagement->saveChild($product->getSku(), $linkedProduct);
        }
        foreach ($linksToDelete as $linkedProduct) {
            $this->linkManagement->removeChild(
                $product->getSku(),
                $option->getOptionId(),
                $linkedProduct->getSku()
            );
        }
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($product, $option->getOptionId(), $linkedProduct);
        }
    }

    /**
     * Compute the difference between given arrays.
     *
     * @param LinkInterface[] $firstArray
     * @param LinkInterface[] $secondArray
     *
     * @return array
     */
    private function compareLinks(array $firstArray, array $secondArray)
    {
        $result = [];

        $firstArrayIds = [];
        $firstArrayMap = [];

        $secondArrayIds = [];

        foreach ($firstArray as $item) {
            $firstArrayIds[] = $item->getId();

            $firstArrayMap[$item->getId()] = $item;
        }

        foreach ($secondArray as $item) {
            $secondArrayIds[] = $item->getId();
        }

        foreach (array_diff($firstArrayIds, $secondArrayIds) as $id) {
            $result[] = $firstArrayMap[$id];
        }

        return $result;
    }
}
