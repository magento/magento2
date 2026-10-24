<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter;

/**
 * Modifies fields mapping before save
 *
 * @api
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
