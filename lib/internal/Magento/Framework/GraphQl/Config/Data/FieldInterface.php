<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Defines contracts for general fields data objects that, combined, represent a configured GraphQL schema.
 */
interface FieldInterface extends StructureInterface
{
    /**
     * Get the type's configured name.
     *
     * @return string
     */
    public function getTypeName() : string;

    /**
     * Return true if argument is a list of input items, otherwise false if it is a single object/scalar.
     *
     * @return bool
     */
    public function isList(): bool;

    /**
     * Return true if argument is required when invoking the query where the argument is specified. False otherwise.
     *
     * @return bool
     */
    public function isRequired(): bool;
}
