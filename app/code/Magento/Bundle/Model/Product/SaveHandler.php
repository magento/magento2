<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Model\Option\SaveAction;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var ProductLinkManagementInterface
     */
    protected $productLinkManagement;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var SaveAction
     */
    private $optionSave;

    /**
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param SaveAction $optionSave
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        OptionRepository $optionRepository,
        ProductLinkManagementInterface $productLinkManagement,
        SaveAction $optionSave,
        MetadataPool $metadataPool = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->productLinkManagement = $productLinkManagement;
        $this->optionSave = $optionSave;
        $this->metadataPool = $metadataPool
            ?: ObjectManager::getInstance()->get(MetadataPool::class);
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
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $bundleProductOptions */
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        //Only processing bundle products.
        if ($entity->getTypeId() !== Type::TYPE_CODE || empty($bundleProductOptions)) {
            return $entity;
        }

        $existingBundleProductOptions = $this->optionRepository->getList($entity->getSku());
        $existingOptionsIds = !empty($existingBundleProductOptions)
            ? $this->getOptionIds($existingBundleProductOptions)
            : [];
        $optionIds = !empty($bundleProductOptions)
            ? $this->getOptionIds($bundleProductOptions)
            : [];

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity, $existingOptionsIds, $optionIds);
            $newOptionsIds = array_diff($optionIds, $existingOptionsIds);
            $this->saveOptions($entity, $bundleProductOptions, $newOptionsIds);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $bundleProductOptions);
            $entity->setCopyFromView(false);
        }

        return $entity;
    }

    /**
     * Remove option product links
     *
     * @param string $entitySku
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return void
     */
    protected function removeOptionLinks($entitySku, $option)
    {
        $links = $option->getProductLinks();
        if (!empty($links)) {
            foreach ($links as $link) {
                $this->productLinkManagement->removeChild($entitySku, $option->getId(), $link->getSku());
            }
        }
    }

    /**
     * Perform save for all options entities.
     *
     * @param object $entity
     * @param array $options
     * @param array $newOptionsIds
     * @return void
     */
    private function saveOptions($entity, array $options, array $newOptionsIds = []): void
    {
        foreach ($options as $option) {
            if (in_array($option->getOptionId(), $newOptionsIds, true)) {
                $option->setOptionId(null);
            }

            $this->optionSave->save($entity, $option);
        }
    }

    /**
     * Get options ids from array of the options entities.
     *
     * @param array $options
     * @return array
     */
    private function getOptionIds(array $options): array
    {
        $optionIds = [];

        if (!empty($options)) {
            /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
            foreach ($options as $option) {
                if ($option->getOptionId()) {
                    $optionIds[] = $option->getOptionId();
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
