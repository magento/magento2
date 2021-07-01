<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Field;

use Magento\Elasticsearch\Model\Script\ScriptInterface;
use Magento\Framework\Search\Request;

interface ScriptResolverInterface
{
    /**
     * @param string $fieldName
     * @param Request\BucketInterface|null $bucket
     * @param string $requestName
     * @return ScriptInterface|null
     */
    public function getFieldAggregationScript(
        string $fieldName,
        ?Request\BucketInterface $bucket,
        string $requestName
    ): ?ScriptInterface;

    /**
     * @param string $fieldName
     * @param Request\FilterInterface $filter
     * @param string $requestName
     * @return ScriptInterface|null
     */
    public function getFieldFilterScript(
        string $fieldName,
        Request\FilterInterface $filter,
        string $requestName
    ): ?ScriptInterface;

    /**
     * @param string $fieldName
     * @param string $direction
     * @param string $requestName
     * @return ScriptInterface|null
     */
    public function getFieldSortScript(string $fieldName, string $direction, string $requestName): ?ScriptInterface;
}
