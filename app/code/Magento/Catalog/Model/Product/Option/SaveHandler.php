<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
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

        /** @var ProductInterface $entity */
        $options = $entity->getOptions();
        $persistedOptions = $this->optionRepository->getProductOptions($entity);

        if ($options) {
            $this->resolveOptionIds($options, $persistedOptions);
            $optionIds = array_map(function (Option $option) {
                return $option->getOptionId();
            }, $options);
        } else {
            $optionIds = [];
        }

        foreach ($persistedOptions as $option) {
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
     * Resolve missing option and value IDs from uniquely matching persisted options
     *
     * @param ProductCustomOptionInterface[] $options
     * @param ProductCustomOptionInterface[] $persistedOptions
     * @return void
     */
    private function resolveOptionIds(array $options, array $persistedOptions): void
    {
        $persistedOptionsById = [];
        foreach ($persistedOptions as $persistedOption) {
            $persistedOptionsById[$persistedOption->getOptionId()] = $persistedOption;
        }

        $resolvedIds = [];
        foreach ($options as $option) {
            if ($option->getOptionId()) {
                $resolvedIds[] = $option->getOptionId();
            }
        }

        foreach ($options as $option) {
            $persistedOption = null;
            if ($option->getOptionId()) {
                $persistedOption = $persistedOptionsById[$option->getOptionId()] ?? null;
            } else {
                $matches = array_filter(
                    $persistedOptions,
                    static fn (ProductCustomOptionInterface $persistedOption): bool =>
                        !in_array($persistedOption->getOptionId(), $resolvedIds)
                        && $persistedOption->getTitle() === $option->getTitle()
                        && $persistedOption->getType() === $option->getType()
                );
                if (count($matches) === 1) {
                    $persistedOption = reset($matches);
                    $option->setOptionId($persistedOption->getOptionId());
                    $resolvedIds[] = $persistedOption->getOptionId();
                }
            }

            if ($persistedOption) {
                $this->resolveOptionValueIds($option, $persistedOption);
            }
        }
    }

    /**
     * Resolve missing value IDs from uniquely matching persisted values
     *
     * @param ProductCustomOptionInterface $option
     * @param ProductCustomOptionInterface $persistedOption
     * @return void
     */
    private function resolveOptionValueIds(
        ProductCustomOptionInterface $option,
        ProductCustomOptionInterface $persistedOption
    ): void {
        $values = $option->getValues() ?? [];
        $persistedValues = $persistedOption->getValues() ?? [];
        $resolvedIds = [];

        foreach ($values as $value) {
            if ($value->getOptionTypeId()) {
                $resolvedIds[] = $value->getOptionTypeId();
            }
        }

        foreach ($values as $value) {
            if ($value->getOptionTypeId()) {
                continue;
            }
            $matches = array_filter(
                $persistedValues,
                static function (ProductCustomOptionValuesInterface $persistedValue) use ($value, $resolvedIds): bool {
                    if (in_array($persistedValue->getOptionTypeId(), $resolvedIds)) {
                        return false;
                    }
                    return $value->getSku() !== null && $value->getSku() !== ''
                        ? $persistedValue->getSku() === $value->getSku()
                        : $persistedValue->getTitle() === $value->getTitle();
                }
            );
            if (count($matches) === 1) {
                $persistedValue = reset($matches);
                $value->setOptionTypeId($persistedValue->getOptionTypeId());
                $resolvedIds[] = $persistedValue->getOptionTypeId();
            }
        }
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
