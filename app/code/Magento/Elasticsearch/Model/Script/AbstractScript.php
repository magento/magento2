<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Script;

abstract class AbstractScript implements ScriptInterface
{
    private const SORT_TYPE_MAP = [
        self::TYPE_BOOLEAN => self::SORT_TYPE_NUMBER,
        self::TYPE_DOUBLE => self::SORT_TYPE_NUMBER,
        self::TYPE_KEYWORD => self::SORT_TYPE_STRING,
        self::TYPE_LONG => self::SORT_TYPE_NUMBER,
    ];

    public function getSortType(): ?string
    {
        return self::SORT_TYPE_MAP[$this->getReturnType()] ?? null;
    }
}
