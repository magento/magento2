<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

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
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        OptionRepository $optionRepository,
        ProductLinkManagementInterface $productLinkManagement,
        MetadataPool $metadataPool = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->productLinkManagement = $productLinkManagement;

        $this->metadataPool = $metadataPool
            ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $options */
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];

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

        $options = $bundleProductOptions ?: [];

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity->getSku(), $existingOptionsIds, $optionIds);

            $newOptionsIds = array_diff($optionIds, $existingOptionsIds);
            $this->saveOptions($entity, $options, $newOptionsIds);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $options);
            $entity->setCopyFromView(false);
        }

        return $entity;
    }

    /**
     * @param string $entitySku
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
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
     * Perform save for all options entities
     *
     * @param object $entity
     * @param array $options
     * @param array $newOptionsIds
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    private function saveOptions($entity, array $options, array $newOptionsIds = [])
    {
        foreach ($options as $option) {
            if (in_array($option->getOptionId(), $newOptionsIds, true)) {
                $option->setOptionId(null);
            }
            $this->optionRepository->save($entity, $option);
        }
    }

    /**
     * Get options ids from array of the options entities
     *
     * @param array $options
     * @return array
     */
    private function getOptionIds(array $options)
    {
        $optionIds = [];

        if (empty($options)) {
            return $optionIds;
        }

        /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
        foreach ($options as $option) {
            if ($option->getOptionId()) {
                $optionIds[] = $option->getOptionId();
            }
        }
        return $optionIds;
    }

    /**
     * Removes old options that no longer exists
     *
     * @param string $entitySku
     * @param array $existingOptionsIds
     * @param array $optionIds
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    private function processRemovedOptions($entitySku, array $existingOptionsIds, array $optionIds)
    {
        foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
            $option = $this->optionRepository->get($entitySku, $optionId);
            $this->removeOptionLinks($entitySku, $option);
            $this->optionRepository->delete($option);
        }
    }
}
