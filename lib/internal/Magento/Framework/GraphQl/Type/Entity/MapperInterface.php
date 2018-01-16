<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Entity;

/**
 * Produces mapped GraphQL type names to their respective entity models.
 */
interface MapperInterface
{
    /**
     * Return GraphQL type names that leverage the given entity name's model.
     *
     * @param string $entityName
     * @return string[]
     */
    public function getMappedTypes(string $entityName);
}
