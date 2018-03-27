<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlatternizer;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
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
     * @var CustomAttributesFlatternizer
     */
    private $customAttributesFlatternizer;

    /**
     * Category constructor.
     * @param CollectionFactory $collectionFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AttributesJoiner $attributesJoiner
     * @param CustomAttributesFlatternizer $customAttributesFlatternizer
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataObjectProcessor $dataObjectProcessor,
        AttributesJoiner $attributesJoiner,
        CustomAttributesFlatternizer $customAttributesFlatternizer
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->attributesJoiner = $attributesJoiner;
        $this->customAttributesFlatternizer = $customAttributesFlatternizer;
    }

    /**
     * @param Field $field
     * @param array|null $value
     * @param array|null $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info)
    {
        $this->categoryIds = array_merge($this->categoryIds, $value[self::PRODUCT_CATEGORY_IDS_KEY]);
        $that = $this;

        return new Deferred(function () use ($that, $value, $info) {
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
                    $categories[$item->getId()] = $this->customAttributesFlatternizer
                        ->flaternize($categories[$item->getId()]);
                    $categories[$item->getId()]['product_count'] = $item->getProductCount();
                }
            }

            return $categories;
        });
    }
}
