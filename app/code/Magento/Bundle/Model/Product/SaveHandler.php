<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @since 2.1.0
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     * @since 2.1.0
     */
    protected $optionRepository;

    /**
     * @var ProductLinkManagementInterface
     * @since 2.1.0
     */
    protected $productLinkManagement;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @param OptionRepository $optionRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param MetadataPool|null $metadataPool
     * @since 2.1.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $options */
        $options = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];

        if ($entity->getTypeId() !== 'bundle' || empty($options)) {
            return $entity;
        }

        if (!$entity->getCopyFromView()) {
            $updatedOptions = [];
            $oldOptions = $this->optionRepository->getList($entity->getSku());

            $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

            $productId = $entity->getData($metadata->getLinkField());

            foreach ($options as $option) {
                $updatedOptions[$option->getOptionId()][$productId] = (bool)$option->getOptionId();
            }

            foreach ($oldOptions as $option) {
                if (!isset($updatedOptions[$option->getOptionId()][$productId])) {
                    $option->setParentId($productId);
                    $this->removeOptionLinks($entity->getSku(), $option);
                    $this->optionRepository->delete($option);
                }
            }
        }

        foreach ($options as $option) {
            $this->optionRepository->save($entity, $option);
        }

        $entity->setCopyFromView(false);

        return $entity;
    }

    /**
     * @param string $entitySku
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return void
     * @since 2.1.0
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
