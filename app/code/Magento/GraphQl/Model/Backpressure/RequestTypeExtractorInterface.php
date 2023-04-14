<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
