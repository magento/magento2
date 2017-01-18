<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
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
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
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
