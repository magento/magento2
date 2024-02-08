<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter\DynamicTemplates;

/**
 * Elasticsearch dynamic templates mapper.
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
