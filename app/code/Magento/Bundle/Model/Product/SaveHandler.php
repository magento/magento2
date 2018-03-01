<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\OptionInterface;
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
     * @param ProductInterface $bundle
     * @param OptionInterface[] $currentOptions
     *
     * @return void
     */
    private function removeOldOptions(
        ProductInterface $bundle,
        array $currentOptions
    ) {
        $oldOptions = $this->optionRepository->getList($bundle->getSku());
        if ($oldOptions) {
            $remainingOptions = [];
            $metadata
                = $this->metadataPool->getMetadata(ProductInterface::class);
            $productId = $bundle->getData($metadata->getLinkField());

            foreach ($currentOptions as $option) {
                $remainingOptions[] = $option->getOptionId();
            }
            foreach ($oldOptions as $option) {
                if (!in_array($option->getOptionId(), $remainingOptions)) {
                    $option->setParentId($productId);
                    $this->removeOptionLinks($bundle->getSku(), $option);
                    $this->optionRepository->delete($option);
                }
            }
        }
    }

    /**
     * @param object $entity
     * @param array $arguments
     *
     * @return ProductInterface|object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $options */
        $options = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];
        //Only processing bundle products.
        if ($entity->getTypeId() !== 'bundle' || empty($options)) {
            return $entity;
        }
        /** @var ProductInterface $entity */
        //Removing old options
        if (!$entity->getCopyFromView()) {
            $this->removeOldOptions($entity, $options);
        }
        //Saving active options.
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
