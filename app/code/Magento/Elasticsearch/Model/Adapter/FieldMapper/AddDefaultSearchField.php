<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldsMappingPreprocessorInterface;

/**
 * Add default search field (catch all field) to the mapping.
 */
class AddDefaultSearchField implements FieldsMappingPreprocessorInterface
{
    /**
     * catch all field name
     */
    private const NAME = '_search';
    /**
     * Add default search field (catch all field) to the fields.
     *
     * Emulates catch all field (_all) for elasticsearch
     *
     * @param array $mapping
     * @return array
     */
    public function process(array $mapping): array
    {
        return [self::NAME => ['type' => 'text']] + $mapping;
    }
}
