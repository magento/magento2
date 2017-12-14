<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Bundle\Model\Product\Type;

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
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     */
    public function __construct(
        OptionRepository $optionRepository,
        ProductLinkManagementInterface $productLinkManagement
    ) {
        $this->optionRepository = $optionRepository;
        $this->productLinkManagement = $productLinkManagement;
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
        if ($entity->getTypeId() !== Type::TYPE_CODE) {
            return $entity;
        }

        $existingBundleProductOptions = $this->optionRepository->getList($entity->getSku());
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions();

        if (empty($existingBundleProductOptions) && empty($bundleProductOptions)) {
            return $entity;
        }

        $existingOptionsIds = [];
        $optionIds = [];
        foreach ($existingBundleProductOptions as $option) {
            $existingOptionsIds[] = $option->getOptionId();
        }
        foreach ($bundleProductOptions as $option) {
            if ($option->getOptionId()) {
                $optionIds[] = $option->getOptionId();
            }
        }

        if (!$entity->getCopyFromView()) {
            foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
                $option = $this->optionRepository->get($entity->getSku(), $optionId);
                $this->removeOptionLinks($entity->getSku(), $option);
                $this->optionRepository->delete($option);
            }
            foreach (array_intersect($optionIds, $existingOptionsIds) as $optionId) {
                $option = $this->optionRepository->get($entity->getSku(), $optionId);
                $this->removeOptionLinks($entity->getSku(), $option);
            }

            $options = $bundleProductOptions ?: [];
            foreach ($options as $option) {
                $this->optionRepository->save($entity, $option);
            }
        } else {
            //save only labels and not selections + product links
            $options = $bundleProductOptions ?: [];
            foreach ($options as $option) {
                $this->optionRepository->save($entity, $option);
                $entity->setCopyFromView(false);
            }
        }
        return $entity;
    }

    /**
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
}
