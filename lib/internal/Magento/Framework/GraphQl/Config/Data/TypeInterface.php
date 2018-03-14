<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Defines contracts for return type data as GraphQL objects.
 */
interface TypeInterface extends StructureInterface
{
    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return Field[]
     */
    public function getFields() : array;
}
