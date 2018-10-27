<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

<<<<<<< HEAD
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Catalog\Api\Data\ProductInterface;
=======
>>>>>>> upstream/2.2-develop
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
     * @param object $entity
     * @param array $arguments
<<<<<<< HEAD
     *
     * @return ProductInterface|object
     *
=======
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
>>>>>>> upstream/2.2-develop
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
<<<<<<< HEAD
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $bundleProductOptions */
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        //Only processing bundle products.
=======
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $options */
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];

>>>>>>> upstream/2.2-develop
        if ($entity->getTypeId() !== Type::TYPE_CODE || empty($bundleProductOptions)) {
            return $entity;
        }

        $existingBundleProductOptions = $this->optionRepository->getList($entity->getSku());
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
        $existingOptionsIds = !empty($existingBundleProductOptions)
            ? $this->getOptionIds($existingBundleProductOptions)
            : [];
        $optionIds = !empty($bundleProductOptions)
            ? $this->getOptionIds($bundleProductOptions)
            : [];
<<<<<<< HEAD

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity->getSku(), $existingOptionsIds, $optionIds);
            $newOptionsIds = array_diff($optionIds, $existingOptionsIds);
            $this->saveOptions($entity, $bundleProductOptions, $newOptionsIds);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $bundleProductOptions);
=======

        $options = $bundleProductOptions ?: [];

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity->getSku(), $existingOptionsIds, $optionIds);

            $newOptionsIds = array_diff($optionIds, $existingOptionsIds);
            $this->saveOptions($entity, $options, $newOptionsIds);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $options);
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
     * Perform save for all options entities.
=======
     * Perform save for all options entities
>>>>>>> upstream/2.2-develop
     *
     * @param object $entity
     * @param array $options
     * @param array $newOptionsIds
<<<<<<< HEAD
     * @return void
     */
    private function saveOptions($entity, array $options, array $newOptionsIds = []): void
=======
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    private function saveOptions($entity, array $options, array $newOptionsIds = [])
>>>>>>> upstream/2.2-develop
    {
        foreach ($options as $option) {
            if (in_array($option->getOptionId(), $newOptionsIds, true)) {
                $option->setOptionId(null);
            }
<<<<<<< HEAD

            $this->optionSave->save($entity, $option);
=======
            $this->optionRepository->save($entity, $option);
>>>>>>> upstream/2.2-develop
        }
    }

    /**
<<<<<<< HEAD
     * Get options ids from array of the options entities.
=======
     * Get options ids from array of the options entities
>>>>>>> upstream/2.2-develop
     *
     * @param array $options
     * @return array
     */
<<<<<<< HEAD
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

=======
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
>>>>>>> upstream/2.2-develop
        return $optionIds;
    }

    /**
<<<<<<< HEAD
     * Removes old options that no longer exists.
=======
     * Removes old options that no longer exists
>>>>>>> upstream/2.2-develop
     *
     * @param string $entitySku
     * @param array $existingOptionsIds
     * @param array $optionIds
<<<<<<< HEAD
     * @return void
     */
    private function processRemovedOptions(string $entitySku, array $existingOptionsIds, array $optionIds): void
=======
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    private function processRemovedOptions($entitySku, array $existingOptionsIds, array $optionIds)
>>>>>>> upstream/2.2-develop
    {
        foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
            $option = $this->optionRepository->get($entitySku, $optionId);
            $this->removeOptionLinks($entitySku, $option);
            $this->optionRepository->delete($option);
        }
    }
}
