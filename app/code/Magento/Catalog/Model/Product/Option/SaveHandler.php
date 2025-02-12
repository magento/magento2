<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * SaveHandler for product option
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var string[]
     */
    private array $compositeProductTypes = ['grouped', 'configurable', 'bundle'];

    /**
     * @var OptionRepository
     */
    protected OptionRepository $optionRepository;

    /**
     * @var Relation
     */
    private $relation;

    /**
     * @param OptionRepository $optionRepository
     * @param Relation|null $relation
     */
    public function __construct(
        OptionRepository $optionRepository,
        ?Relation        $relation = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->relation = $relation ?: ObjectManager::getInstance()->get(Relation::class);
    }

    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws CouldNotSaveException
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getOptionsSaved()) {
            return $entity;
        }

        $options = $entity->getOptions();
        $optionIds = [];

        if ($options) {
            $optionIds = array_map(function (Option $option) {
                return $option->getOptionId();
            }, $options);
        }

        /** @var ProductInterface $entity */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            if (!in_array($option->getOptionId(), $optionIds)) {
                $this->optionRepository->delete($option);
            }
        }
        if ($options) {
            $this->processOptionsSaving($options, (bool)$entity->dataHasChangedFor('sku'), $entity);
        }

        return $entity;
    }

    /**
     * Save custom options
     *
     * @param array $options
     * @param bool $hasChangedSku
     * @param ProductInterface $product
     * @return void
     * @throws CouldNotSaveException
     */
    private function processOptionsSaving(array $options, bool $hasChangedSku, ProductInterface $product): void
    {
        $isProductHasRelations = $this->isProductHasRelations($product);
        /** @var ProductCustomOptionInterface $option */
        foreach ($options as $option) {
            if (!$isProductHasRelations && $option->getIsRequire()) {
                $message = 'Required custom options cannot be added to a simple product'
                    . ' that is a part of a composite product.';
                throw new CouldNotSaveException(__($message));
            }

            if ($hasChangedSku && $option->hasData('product_sku')) {
                $option->setProductSku($product->getSku());
            }
            $this->optionRepository->save($option);
        }
    }

    /**
     * Check if product doesn't belong to composite product
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function isProductHasRelations(ProductInterface $product): bool
    {
        $result = true;
        if (!in_array($product->getTypeId(), $this->compositeProductTypes)
            && $this->relation->getRelationsByChildren([$product->getId()])
        ) {
            $result = false;
        }
        return $result;
    }
}
