<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\RuntimeException;

/**
 * Format attributes for GraphQL output
 */
interface GetAttributeDataInterface
{
    /**
     * Retrieve formatted attribute metadata
     *
     * @param AttributeInterface $attribute
     * @param string $entityType
     * @param int $storeId
     * @return array
     * @throws RuntimeException
     */
    public function execute(AttributeInterface $attribute, string $entityType, int $storeId): array;
}
