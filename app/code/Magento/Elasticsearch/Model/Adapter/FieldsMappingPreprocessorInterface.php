<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter;

/**
 * Modifies fields mapping before save
 */
interface FieldsMappingPreprocessorInterface
{
    /**
     * Modifies fields mapping before save
     *
     * @param array $mapping
     * @return array
     */
    public function process(array $mapping): array;
}
