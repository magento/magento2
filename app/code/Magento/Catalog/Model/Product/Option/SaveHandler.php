<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
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
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getOptionsSaved()) {
            return $entity;
        }

        $options = $entity->getOptions();
        $optionIds = [];

        if ($options) {
            $optionIds = array_map(function ($option) {
                /** @var \Magento\Catalog\Model\Product\Option $option */
                return $option->getOptionId();
            }, $options);
        }

        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            if (!in_array($option->getOptionId(), $optionIds)) {
                $this->optionRepository->delete($option);
            }
        }
        if ($options) {
            $this->processOptionsSaving($options, (bool)$entity->dataHasChangedFor('sku'), (string)$entity->getSku());
        }

        return $entity;
    }

    /**
     * Save custom options
     *
     * @param array $options
     * @param bool $hasChangedSku
     * @param string $newSku
     */
    private function processOptionsSaving(array $options, bool $hasChangedSku, string $newSku)
    {
        foreach ($options as $option) {
            if ($hasChangedSku && $option->hasData('product_sku')) {
                $option->setProductSku($newSku);
            }
            $this->optionRepository->save($option);
        }
    }
}
