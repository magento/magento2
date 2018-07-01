<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Retrieves breadcrumbs
 */
class Breadcrumbs implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): Value
    {
        $breadcrumbs = [];

        if (!isset($value['path'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $categoryPath = $value['path'];
        $pathCategoryIds = explode('/', $categoryPath);
        $parentCategoryIds = array_slice($pathCategoryIds, 2, count($pathCategoryIds) - 3);

        if (count($parentCategoryIds)) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect(['name', 'url_key']);
            $collection->addAttributeToFilter('entity_id', $parentCategoryIds);

            foreach ($collection as $category) {
                $breadcrumbs[] = [
                    'category_id'      => $category->getId(),
                    'category_name'    => $category->getName(),
                    'category_level'   => $category->getLevel(),
                    'category_url_key' => $category->getUrlKey(),
                ];
            }
        }

        $result = function () use ($breadcrumbs) {
            return count($breadcrumbs) ? $breadcrumbs : null;
        };

        return $this->valueFactory->create($result);
    }
}