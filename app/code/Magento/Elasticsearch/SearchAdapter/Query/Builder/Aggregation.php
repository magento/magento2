<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Script;
use Magento\Elasticsearch\Model\Script\ScriptInterface;
use Magento\Elasticsearch\SearchAdapter\Field;

/**
 * @api
 * @since 100.1.0
 */
class Aggregation
{
    /**
     * Max number of results returned per single term bucket, i.e. limit of options for layered navigation filter.
     * Default ElasticSearch limit is 10
     *
     * @var int
     */
    private static $maxTermBacketSize = 500;

    /**
     * @var FieldMapperInterface
     * @since 100.1.0
     */
    protected $fieldMapper;

    /**
     * @var Script\BuilderInterface
     */
    private $scriptBuilder;

    /**
     * @var Field\ScriptResolverPoolInterface
     */
    private $fieldScriptResolverPool;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param Script\BuilderInterface|null $scriptBuilder
     * @param Field\ScriptResolverPoolInterface|null $fieldScriptResolverPool
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        ?Script\BuilderInterface $scriptBuilder = null,
        ?Field\ScriptResolverPoolInterface $fieldScriptResolverPool = null
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->scriptBuilder = $scriptBuilder ?? ObjectManager::getInstance()
            ->get(Script\Builder::class);
        $this->fieldScriptResolverPool = $fieldScriptResolverPool ?? ObjectManager::getInstance()
            ->get(Field\ScriptResolverPoolInterface::class);
    }

    /**
     * Build aggregation query for request
     *
     * @param RequestInterface $request
     * @param array $searchQuery
     * @return array
     * @since 100.1.0
     */
    public function build(
        RequestInterface $request,
        array $searchQuery
    ) {
        $buckets = $request->getAggregation();
        foreach ($buckets as $bucket) {
            $fieldScriptResolver = $this->fieldScriptResolverPool->getFieldScriptResolver($bucket->getField());

            $fieldScript = !$fieldScriptResolver
                ? null
                : $fieldScriptResolver->getFieldAggregationScript($bucket->getField(), $bucket, $request->getName());

            $searchQuery = !$fieldScript
                ? $this->buildBucket($searchQuery, $bucket)
                : $this->buildScriptedBucket($searchQuery, $bucket, $fieldScript);
        }
        return $searchQuery;
    }

    /**
     * Build aggregation query for bucket
     *
     * @param array $searchQuery
     * @param BucketInterface $bucket
     * @return array
     * @since 100.1.0
     */
    protected function buildBucket(
        array $searchQuery,
        BucketInterface $bucket
    ) {
        $field = $this->fieldMapper->getFieldName($bucket->getField());
        switch ($bucket->getType()) {
            case BucketInterface::TYPE_TERM:
                $searchQuery['body']['aggregations'][$bucket->getName()]= [
                    'terms' => [
                        'field' => $field,
                        'size' => self::$maxTermBacketSize,
                    ],
                ];
                break;
            case BucketInterface::TYPE_DYNAMIC:
                $searchQuery['body']['aggregations'][$bucket->getName()] = [
                    'extended_stats' => [
                        'field' => $field,
                    ],
                ];;
        }
        return $searchQuery;
    }

    /**
     * @see buildBucket()
     *
     * @param array $searchQuery
     * @param BucketInterface $bucket
     * @param ScriptInterface $script
     */
    protected function buildScriptedBucket(
        array $searchQuery,
        BucketInterface $bucket,
        ScriptInterface $script
    ) {
        $field = $this->fieldMapper->getFieldName($bucket->getField());
        switch ($bucket->getType()) {
            case BucketInterface::TYPE_TERM:
                $searchQuery['body']['aggregations'][$bucket->getName()]= [
                    'terms' => [
                        'field' => $field,
                        'size' => self::$maxTermBacketSize,
                        'script' => $this->scriptBuilder->buildScript($script),
                    ],
                ];
                break;
            case BucketInterface::TYPE_DYNAMIC:
                $searchQuery['body']['aggregations'][$bucket->getName()] = [
                    'extended_stats' => [
                        'field' => $field,
                        'script' => $this->scriptBuilder->buildScript($script),
                    ],
                ];
                break;
        }
        return $searchQuery;
    }
}
