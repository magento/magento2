<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Option;

use Exception;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Api\ProductLinkManagementAddChildrenInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Encapsulates logic for saving a bundle option, including coalescing the parent product's data.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ProductLinkManagementAddChildrenInterface
     */
    private $addChildren;

    /**
     * @param Option $optionResource
     * @param MetadataPool $metadataPool
     * @param Type $type
     * @param ProductLinkManagementInterface $linkManagement
     * @param StoreManagerInterface|null $storeManager
     * @param ProductLinkManagementAddChildrenInterface|null $addChildren
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Option $optionResource,
        MetadataPool $metadataPool,
        Type $type,
        ProductLinkManagementInterface $linkManagement,
        ?StoreManagerInterface $storeManager = null,
        ?ProductLinkManagementAddChildrenInterface $addChildren = null
    ) {
        $this->optionResource = $optionResource;
        $this->metadataPool = $metadataPool;
        $this->type = $type;
        $this->linkManagement = $linkManagement;
        $this->addChildren = $addChildren ?:
            ObjectManager::getInstance()->get(ProductLinkManagementAddChildrenInterface::class);
    }

    /**
     * Bulk options save
     *
     * @param ProductInterface $bundleProduct
     * @param array $options
     * @param array $existingBundleProductOptions
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function saveBulk(
        ProductInterface $bundleProduct,
        array $options,
        array $existingBundleProductOptions = []
    ): void {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $optionCollection = $this->type->getOptionsCollection($bundleProduct);

        foreach ($options as $option) {
            $this->saveOptionItem(
                $bundleProduct,
                $option,
                $optionCollection,
                $metadata,
                $existingBundleProductOptions
            );
        }

        $bundleProduct->setIsRelationsChanged(true);
    }

    /**
     * Process option save
     *
     * @param ProductInterface $bundleProduct
     * @param OptionInterface $option
     * @param Collection $optionCollection
     * @param EntityMetadataInterface $metadata
     * @param array $existingBundleProductOptions
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function saveOptionItem(
        ProductInterface $bundleProduct,
        OptionInterface $option,
        Collection $optionCollection,
        EntityMetadataInterface $metadata,
        array $existingBundleProductOptions = []
    ) : void {
        $linksToAdd = [];

        $option->setStoreId($bundleProduct->getStoreId());
        $parentId = $bundleProduct->getData($metadata->getLinkField());
        $option->setParentId($parentId);
        $optionId = $option->getOptionId();
        $existingOption = $this->retrieveExistingOption($optionCollection, $option, $existingBundleProductOptions);

        if (!$optionId || $existingOption->getParentId() != $parentId) {
            $option->setOptionId(null);
            $option->setDefaultTitle($option->getTitle());
            if (is_array($option->getProductLinks())) {
                $linksToAdd = $option->getProductLinks();
            }
        } else {
            if (!$existingOption || !$existingOption->getOptionId()) {
                throw new NoSuchEntityException(
                    __("The option that was requested doesn't exist. Verify the entity and try again.")
                );
            }

            $option->setData(array_merge($existingOption->getData(), $option->getData()));
            $this->updateOptionSelection($bundleProduct, $option, $existingOption);
        }

        try {
            $this->optionResource->save($option);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__("The option couldn't be saved."), $e);
        }

        /** @var LinkInterface $linkedProduct */
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($bundleProduct, $option->getOptionId(), $linkedProduct);
        }
    }

    /**
     * Manage the logic of saving a bundle option, including the coalescence of its parent product data.
     *
     * @param ProductInterface $bundleProduct
     * @param OptionInterface $option
     * @return OptionInterface
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function save(ProductInterface $bundleProduct, OptionInterface $option)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $optionCollection = $this->type->getOptionsCollection($bundleProduct);

        $this->saveOptionItem($bundleProduct, $option, $optionCollection, $metadata);

        return $option;
    }

    /**
     * Update option selections
     *
     * @param ProductInterface $product
     * @param OptionInterface $option
     * @param OptionInterface|null $existingOption
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function updateOptionSelection(
        ProductInterface $product,
        OptionInterface $option,
        ?OptionInterface $existingOption = null
    ):void {
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
            if (!empty($existingOption) && !empty($existingOption->getProductLinks())) {
                $linksToDelete = $this->compareLinks($existingOption->getProductLinks(), $linksToUpdate);
                $linksToUpdate = $this->verifyLinksToUpdate($existingOption->getProductLinks(), $linksToUpdate);
            }
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

        $this->addChildren->addChildren($product, (int)$option->getOptionId(), $linksToAdd);
    }

    /**
     * Verify that updated data actually changed
     *
     * @param LinkInterface[] $existing
     * @param LinkInterface[] $updates
     * @return array
     */
    private function verifyLinksToUpdate(array $existing, array $updates) : array
    {
        $linksToUpdate = [];
        $beforeLinksMap = [];

        foreach ($existing as $beforeLink) {
            $beforeLinksMap[$beforeLink->getId()] = $beforeLink;
        }

        foreach ($updates as $updatedLink) {
            if (array_key_exists($updatedLink->getId(), $beforeLinksMap)) {
                $beforeLink = $beforeLinksMap[$updatedLink->getId()];
                if ($this->isLinkChanged($beforeLink, $updatedLink)) {
                    $linksToUpdate[] = $updatedLink;
                }
            } else {
                $linksToUpdate[] = $updatedLink;
            }
        }
        return $linksToUpdate;
    }

    /**
     * Check is updated link actually updated
     *
     * @param LinkInterface $beforeLink
     * @param LinkInterface $updatedLink
     * @return bool
     */
    private function isLinkChanged(LinkInterface $beforeLink, LinkInterface $updatedLink) : bool
    {
        return (int)$beforeLink->getOptionId() !== (int)$updatedLink->getOptionId()
            || $beforeLink->getIsDefault() !== $updatedLink->getIsDefault()
            || (float)$beforeLink->getQty() !== (float)$updatedLink->getQty()
            || $beforeLink->getPrice() !== $updatedLink->getPrice()
            || $beforeLink->getCanChangeQuantity() !== $updatedLink->getCanChangeQuantity()
            || (array)$beforeLink->getExtensionAttributes() !== (array)$updatedLink->getExtensionAttributes()
            || (int)$beforeLink->getPosition() !== (int)$updatedLink->getPosition()
            || $beforeLink->getSku() !== $updatedLink->getSku()
            || $beforeLink->getPriceType() !== $updatedLink->getPriceType();
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

    /**
     * Retrieve option from list.
     *
     * @param Collection $optionCollection
     * @param OptionInterface $option
     * @param array $existingBundleProductOptions
     * @return OptionInterface
     */
    private function retrieveExistingOption(
        Collection $optionCollection,
        OptionInterface $option,
        array $existingBundleProductOptions
    ): OptionInterface {
        $existingOption = $optionCollection->getItemById($option->getOptionId());

        $incomingOption = current(
            array_filter($existingBundleProductOptions, function ($obj) use ($option) {
                return $obj->getData()['option_id'] == $option->getId();
            })
        );

        if (!empty($incomingOption)) {
            $existingOption->setData(
                array_merge(
                    $existingOption->getData(),
                    $incomingOption->getData()
                )
            );
        }

        // @phpstan-ignore-next-line
        if (empty($existingOption)) {
            $existingOption = $optionCollection->getNewEmptyItem();
        }

        return $existingOption;
    }
}
