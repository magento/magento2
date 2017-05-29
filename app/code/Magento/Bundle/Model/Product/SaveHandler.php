<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\ProductLinkManagementInterface;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions();
        if ($entity->getTypeId() !== 'bundle' || empty($bundleProductOptions)) {
            return $entity;
        }

        if (!$entity->getCopyFromView()) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
            foreach ($this->optionRepository->getList($entity->getSku()) as $option) {
                $this->removeOptionLinks($entity->getSku(), $option);
                $this->optionRepository->delete($option);
            }

            $options = $bundleProductOptions ?: [];
            foreach ($options as $option) {
                $option->setOptionId(null);
                $this->optionRepository->save($entity, $option);
            }
        } else {
            //save only labels and not selections + product links
            $options = $bundleProductOptions ?: [];
            foreach ($options as $option) {
                $this->optionRepository->save($entity, $option);
            }
            $entity->setCopyFromView(false);
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
