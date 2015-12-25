<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ProcessEntityRelationInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;

/**
 * Class SaveHandler
 */
class SaveHandler
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

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
     * @param MetadataPool $metadataPool
     * @param ProductLinkManagementInterface $productLinkManagement
     */
    public function __construct(
        OptionRepository $optionRepository,
        MetadataPool $metadataPool,
        ProductLinkManagementInterface $productLinkManagement
    ) {
        $this->optionRepository = $optionRepository;
        $this->metadataPool = $metadataPool;
        $this->productLinkManagement = $productLinkManagement;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions();
        if ($entity->getTypeId() !== 'bundle' || $bundleProductOptions === null) {
            return $entity;
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->optionRepository->getList($entity->getSku()) as $option) {
            $this->removeOptionLinks($entity->getSku(), $option);
            $this->optionRepository->delete($option);
        }

        $options = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        foreach ($options as $option) {
            $option->setOptionId(null);
            $this->optionRepository->save($entity, $option);
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
