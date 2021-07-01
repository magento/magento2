<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Script;
use Magento\Elasticsearch\SearchAdapter\Field;
use Magento\Framework\App\ObjectManager;
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
     * @var array
     */
    private $skippedFields;

    /**
     * @var array
     */
    private $map;

    /**
     * @var Script\BuilderInterface
     */
    private $scriptBuilder;

    /**
     * @var Field\ScriptResolverPoolInterface
     */
    private $fieldScriptResolverPool;

    /**
     * @param AttributeProvider $attributeAdapterProvider
     * @param FieldNameResolver $fieldNameResolver
     * @param array $skippedFields
     * @param array $map
     * @param Script\BuilderInterface|null $scriptBuilder
     * @param Field\ScriptResolverPoolInterface|null $fieldScriptResolverPool
     */
    public function __construct(
        AttributeProvider $attributeAdapterProvider,
        FieldNameResolver $fieldNameResolver,
        array $skippedFields = [],
        array $map = [],
        ?Script\BuilderInterface $scriptBuilder = null,
        ?Field\ScriptResolverPoolInterface $fieldScriptResolverPool = null
    ) {
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->skippedFields = array_merge(self::DEFAULT_SKIPPED_FIELDS, $skippedFields);
        $this->map = array_merge(self::DEFAULT_MAP, $map);
        $this->scriptBuilder = $scriptBuilder ?? ObjectManager::getInstance()
            ->get(Script\Builder::class);
        $this->fieldScriptResolverPool = $fieldScriptResolverPool ?? ObjectManager::getInstance()
            ->get(Field\ScriptResolverPoolInterface::class);
    }

    /**
     * Prepare sort.
     *
     * @param RequestInterface $request
     * @return array
     */
    public function getSort(RequestInterface $request)
    {
        $sorts = [];
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

            $fieldScriptResolver = $this->fieldScriptResolverPool->getFieldScriptResolver($item['field']);

            $fieldScript = !$fieldScriptResolver
                ? null
                : $fieldScriptResolver->getFieldSortScript($item['field'], $item['direction'], $request->getName());

            if ($fieldScript) {
                $sorts[] = [
                    '_script' => [
                        'script' => $this->scriptBuilder->buildScript($fieldScript),
                        'type' => $fieldScript->getSortType(),
                        'order' => strtolower($item['direction']),
                    ],
                ];

                continue;
            }

            $attribute = $this->attributeAdapterProvider->getByAttributeCode($item['field']);
            $fieldName = $this->fieldNameResolver->getFieldName($attribute);
            if (isset($this->map[$fieldName])) {
                $fieldName = $this->map[$fieldName];
            }
            if ($attribute->isSortable() && !($attribute->isFloatType() || $attribute->isIntegerType())) {
                $suffix = $this->fieldNameResolver->getFieldName(
                    $attribute,
                    ['type' => FieldMapperInterface::TYPE_SORT]
                );
                $fieldName .= '.' . $suffix;
            }
            $sorts[] = [
                $fieldName => [
                    'order' => strtolower($item['direction'])
                ]
            ];
        }

        return $sorts;
    }
}
