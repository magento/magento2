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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * SaveHandler for product option
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

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
        ?Relation $relation = null
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
     * Check if product doesn't belong to composite product
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function isProductValid(ProductInterface $product): bool
    {
        $result = true;
        if ($product->getTypeId() !== Type::TYPE_BUNDLE
            && $product->getTypeId() !== Configurable::TYPE_CODE
            && $product->getTypeId() !== Grouped::TYPE_CODE
            && $this->relation->getRelationsByChildren([$product->getId()])
        ) {
            $result = false;
        }

        return $result;
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
        $isProductValid = $this->isProductValid($product);
        /** @var ProductCustomOptionInterface $option */
        foreach ($options as $option) {
            if (!$isProductValid && $option->getIsRequire()) {
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
}
