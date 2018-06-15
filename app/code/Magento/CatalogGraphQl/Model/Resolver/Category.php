<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Category field resolver, used for GraphQL request processing.
 */
class Category implements ResolverInterface
{
    /**
     * Product category ids
     */
    const PRODUCT_CATEGORY_IDS_KEY = 'category_ids';

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
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

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
     * Category constructor.
     * @param CollectionFactory $collectionFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AttributesJoiner $attributesJoiner
     * @param CustomAttributesFlattener $customAttributesFlattener
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataObjectProcessor $dataObjectProcessor,
        AttributesJoiner $attributesJoiner,
        CustomAttributesFlattener $customAttributesFlattener,
        ValueFactory $valueFactory
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->attributesJoiner = $attributesJoiner;
        $this->customAttributesFlattener = $customAttributesFlattener;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $this->categoryIds = array_merge($this->categoryIds, $value[self::PRODUCT_CATEGORY_IDS_KEY]);
        $that = $this;

        return $this->valueFactory->create(function () use ($that, $value, $info) {
            $categories = [];
            if (empty($that->categoryIds)) {
                return [];
            }

            if (!$this->collection->isLoaded()) {
                $that->attributesJoiner->join($info->fieldASTs[0], $this->collection);
                $this->collection->addIdFilter($this->categoryIds);
            }
            /** @var CategoryInterface | \Magento\Catalog\Model\Category $item */
            foreach ($this->collection as $item) {
                if (in_array($item->getId(), $value[$that::PRODUCT_CATEGORY_IDS_KEY])) {
                    $categories[$item->getId()] = $this->dataObjectProcessor->buildOutputDataArray(
                        $item,
                        CategoryInterface::class
                    );
                    $categories[$item->getId()] = $this->customAttributesFlattener
                        ->flatten($categories[$item->getId()]);
                    $categories[$item->getId()]['product_count'] = $item->getProductCount();
                }
            }

            return $categories;
        });
    }
}
