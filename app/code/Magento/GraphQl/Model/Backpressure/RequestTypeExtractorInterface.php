<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Extracts request type for fields
 */
interface RequestTypeExtractorInterface
{
    /**
     * Extracts type ID if possible
     *
     * @param Field $field
     * @return string|null
     */
    public function extract(Field $field): ?string;
}
