<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\InventoryFieldsProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Search\RequestInterface;

/**
 * Sort builder.
 */
class Sort
{
    /**
     * List of fields that need to skipp by default.
     */
    private const DEFAULT_SKIPPED_FIELDS = [
        'entity_id',
    ];

    /**
     * Default mapping for special fields.
     */
    private const DEFAULT_MAP = [
        'relevance' => '_score',
    ];

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $skippedFields;

    /**
     * @var array
     */
    private $map;

    /**
     * @param AttributeProvider $attributeAdapterProvider
     * @param FieldNameResolver $fieldNameResolver
     * @param array $skippedFields
     * @param array $map
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        AttributeProvider $attributeAdapterProvider,
        FieldNameResolver $fieldNameResolver,
        array $skippedFields = [],
        array $map = [],
        ScopeConfigInterface  $scopeConfig = null
    ) {
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->skippedFields = array_merge(self::DEFAULT_SKIPPED_FIELDS, $skippedFields);
        $this->map = array_merge(self::DEFAULT_MAP, $map);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Prepare sort.
     *
     * @param RequestInterface $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSort(RequestInterface $request)
    {
        $sorts = $this->getSortBySaleability();

        /**
         * Temporary solution for an existing interface of a fulltext search request in Backward compatibility purposes.
         * Scope to split Search request interface on two different 'Search' and 'Fulltext Search' contains in MC-16461.
         */
        if (!method_exists($request, 'getSort')) {
            return $sorts;
        }
        foreach ($request->getSort() as $item) {
            if (in_array($item['field'], $this->skippedFields)) {
                continue;
            }
            $attribute = $this->attributeAdapterProvider->getByAttributeCode((string)$item['field']);
            $fieldName = $this->fieldNameResolver->getFieldName($attribute);
            if (isset($this->map[$fieldName])) {
                $fieldName = $this->map[$fieldName];
            }
            if ($attribute->isSortable() &&
                !$attribute->isComplexType() &&
                !($attribute->isFloatType() || $attribute->isIntegerType())
            ) {
                $suffix = $this->fieldNameResolver->getFieldName(
                    $attribute,
                    ['type' => FieldMapperInterface::TYPE_SORT]
                );
                $fieldName .= '.' . $suffix;
            }
            if ($attribute->isComplexType() && $attribute->isSortable()) {
                $fieldName .= '_value';
                $suffix = $this->fieldNameResolver->getFieldName(
                    $attribute,
                    ['type' => FieldMapperInterface::TYPE_SORT]
                );
                $fieldName .= '.' . $suffix;
            }
            $sorts[] = [
                $fieldName => [
                    'order' => strtolower($item['direction'] ?? '')
                ]
            ];
        }

        return $sorts;
    }

    /**
     * Prepare sort by saleability.
     *
     * @return array
     */
    private function getSortBySaleability()
    {
        $sorts = [];

        if ($this->hasShowOutOfStockStatus()) {
            $attribute = $this->attributeAdapterProvider->getByAttributeCode(InventoryFieldsProvider::IS_SALABLE);
            $fieldName = $this->fieldNameResolver->getFieldName($attribute);
            $sorts[] = [
                $fieldName => [
                    'order' => Collection::SORT_ORDER_DESC
                ]
            ];
        }

        return $sorts;
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function hasShowOutOfStockStatus(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
