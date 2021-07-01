<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Field;

interface ScriptResolverPoolInterface
{
    /**
     * @return ScriptResolverPoolInterface[]
     */
    public function getScriptResolvers();

    /**
     * @param string $fieldName
     * @return ScriptResolverInterface|null
     */
    public function getFieldScriptResolver(string $fieldName): ?ScriptResolverInterface;
}
