<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Bundle\Model\ProductRelationsProcessorComposite;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Bundle product save handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var ProductLinkManagementInterface
     */
    private $productLinkManagement;

    /**
     * @var SaveAction
     */
    private $optionSave;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var CheckOptionLinkIfExist
     */
    private $checkOptionLinkIfExist;

    /**
     * @var ProductRelationsProcessorComposite
     */
    private $productRelationsProcessorComposite;

    /**
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param SaveAction $optionSave
     * @param MetadataPool $metadataPool
     * @param CheckOptionLinkIfExist|null $checkOptionLinkIfExist
     * @param ProductRelationsProcessorComposite|null $productRelationsProcessorComposite
     */
    public function __construct(
        OptionRepository $optionRepository,
        ProductLinkManagementInterface $productLinkManagement,
        SaveAction $optionSave,
        MetadataPool $metadataPool,
        ?CheckOptionLinkIfExist $checkOptionLinkIfExist = null,
        ?ProductRelationsProcessorComposite $productRelationsProcessorComposite = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->productLinkManagement = $productLinkManagement;
        $this->optionSave = $optionSave;
        $this->metadataPool = $metadataPool;
        $this->checkOptionLinkIfExist = $checkOptionLinkIfExist
            ?? ObjectManager::getInstance()->get(CheckOptionLinkIfExist::class);
        $this->productRelationsProcessorComposite = $productRelationsProcessorComposite
            ?? ObjectManager::getInstance()->get(ProductRelationsProcessorComposite::class);
    }

    /**
     * Perform action on Bundle product relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     *
     * @return ProductInterface|object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var OptionInterface[] $bundleProductOptions */
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        //Only processing bundle products.
        if ($entity->getTypeId() !== Type::TYPE_CODE || empty($bundleProductOptions)) {
            return $entity;
        }

        $existingBundleProductOptions = $this->optionRepository->getList($entity->getSku());
        $existingOptionsIds = !empty($existingBundleProductOptions)
            ? $this->getOptionIds($existingBundleProductOptions)
            : [];
        $optionIds = $this->getOptionIds($bundleProductOptions);

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity, $existingOptionsIds, $optionIds);
            $this->saveOptions($entity, $bundleProductOptions, $existingBundleProductOptions);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $bundleProductOptions);
            $entity->setCopyFromView(false);
        }

        $this->productRelationsProcessorComposite->process(
            $entity,
            $existingBundleProductOptions,
            $bundleProductOptions
        );

        return $entity;
    }

    /**
     * Remove option product links
     *
     * @param string $entitySku
     * @param OptionInterface $option
     *
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function removeOptionLinks($entitySku, $option)
    {
        $links = $option->getProductLinks();
        if (!empty($links)) {
            foreach ($links as $link) {
                $linkCanBeDeleted = $this->checkOptionLinkIfExist->execute($entitySku, $option, $link);
                if ($linkCanBeDeleted) {
                    $this->productLinkManagement->removeChild($entitySku, $option->getId(), $link->getSku());
                }
            }
        }
    }

    /**
     * Perform save for all options entities.
     *
     * @param ProductInterface $entity
     * @param array $options
     * @param array $existingBundleProductOptions
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveOptions(
        ProductInterface $entity,
        array $options,
        array $existingBundleProductOptions = []
    ): void {
        $this->optionSave->saveBulk($entity, $options, $existingBundleProductOptions);
    }

    /**
     * Get options ids from array of the options entities.
     *
     * @param array $options
     *
     * @return array
     */
    private function getOptionIds(array $options): array
    {
        $optionIds = [];

        if (!empty($options)) {
            /** @var OptionInterface $option */
            foreach ($options as $option) {
                if ($option->getOptionId()) {
                    $optionIds[] = (int)$option->getOptionId();
                }
            }
        }

        return $optionIds;
    }

    /**
     * Removes old options that no longer exists.
     *
     * @param ProductInterface $entity
     * @param array $existingOptionsIds
     * @param array $optionIds
     *
     * @return void
     */
    private function processRemovedOptions(ProductInterface $entity, array $existingOptionsIds, array $optionIds): void
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $parentId = $entity->getData($metadata->getLinkField());
        foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
            $option = $this->optionRepository->get($entity->getSku(), $optionId);
            $option->setParentId($parentId);
            $this->removeOptionLinks($entity->getSku(), $option);
            $this->optionRepository->delete($option);
        }
    }
}
