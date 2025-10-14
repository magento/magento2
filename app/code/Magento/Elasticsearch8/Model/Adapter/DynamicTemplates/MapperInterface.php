<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Adapter\DynamicTemplates;

/**
 * Elasticsearch dynamic templates mapper.
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
interface MapperInterface
{
    /**
     * Add/remove/edit dynamic template mapping.
     *
     * @param array $templates
     * @return array
     */
    public function processTemplates(array $templates): array;
}
