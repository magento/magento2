<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Entity;

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
    public function getMappedTypes(string $entityName) : array;
}
