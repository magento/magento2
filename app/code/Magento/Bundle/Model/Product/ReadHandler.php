<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * ReadHandler constructor.
     *
     * @param OptionRepository $optionRepository
     */
    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $options = $this->optionRepository->getListByProduct($entity);
        if ($options) {
            $entityExtension->setBundleProductOptions($options);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
