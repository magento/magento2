<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

/**
 * Generate configured top level GraphQL types, and their children types as defined by type handlers
 */
interface GeneratorInterface
{
    /**
     * Generate type definitions for all fields of given GraphQl query or mutation name
     *
     * @param string $typeName
     * @return array
     */
    public function generateTypes(string $typeName);
}
