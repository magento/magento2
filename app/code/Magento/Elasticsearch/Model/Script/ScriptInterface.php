<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Script;

interface ScriptInterface
{
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_DOUBLE = 'double';
    const TYPE_GEO_POINT = 'geo_point';
    const TYPE_IP = 'ip';
    const TYPE_KEYWORD = 'keyword';
    const TYPE_LONG = 'long';

    const SORT_TYPE_NUMBER = 'number';
    const SORT_TYPE_STRING = 'string';

    /**
     * @return string
     */
    public function getLang(): string;

    /**
     * @return string
     */
    public function getReturnType(): string;

    /**
     * @return string|null
     */
    public function getSortType(): ?string;

    /**
     * @return string
     */
    public function getSource(): string;

    /**
     * @return string[]
     */
    public function getParameterNames(): array;
}
