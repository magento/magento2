<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\CatalogGraphQl\Model\Category\Hydrator as CategoryHydrator;

/**
 * Resolver for category objects the product is assigned to.
 */
class Categories implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * Accumulated category ids
     *
     * @var array
     */
    private $categoryIds = [];

    /**
     * @var AttributesJoiner
     */
    private $attributesJoiner;

    /**
     * @var CustomAttributesFlattener
     */
    private $customAttributesFlattener;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var CategoryHydrator
     */
    private $categoryHydrator;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param CustomAttributesFlattener $customAttributesFlattener
     * @param ValueFactory $valueFactory
     * @param CategoryHydrator $categoryHydrator
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        CustomAttributesFlattener $customAttributesFlattener,
        ValueFactory $valueFactory,
        CategoryHydrator $categoryHydrator
    ) {
        $this->collection = $collectionFactory->create();
        $this->attributesJoiner = $attributesJoiner;
        $this->customAttributesFlattener = $customAttributesFlattener;
        $this->valueFactory = $valueFactory;
        $this->categoryHydrator = $categoryHydrator;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];
        $categoryIds = $product->getCategoryIds();
        $this->categoryIds = array_merge($this->categoryIds, $categoryIds);
        $that = $this;

        return $this->valueFactory->create(function () use ($that, $categoryIds, $info) {
            $categories = [];
            if (empty($that->categoryIds)) {
                return [];
            }

            if (!$this->collection->isLoaded()) {
                $that->attributesJoiner->join($info->fieldNodes[0], $this->collection);
                $this->collection->addIdFilter($this->categoryIds);
            }
            /** @var CategoryInterface | \Magento\Catalog\Model\Category $item */
            foreach ($this->collection as $item) {
                if (in_array($item->getId(), $categoryIds)) {
                    // Try to extract all requested fields from the loaded collection data
                    $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item, true);
                    $categories[$item->getId()]['model'] = $item;
                    $requestedFields = $that->attributesJoiner->getQueryFields($info->fieldNodes[0]);
                    $extractedFields = array_keys($categories[$item->getId()]);
                    $foundFields = array_intersect($requestedFields, $extractedFields);
                    if (count($requestedFields) === count($foundFields)) {
                        continue;
                    }

                    // If not all requested fields were extracted from the collection, start more complex extraction
                    $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item);
                }
            }

            return $categories;
        });
    }
}
