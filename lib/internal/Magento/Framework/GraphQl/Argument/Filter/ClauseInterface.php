<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Argument\Filter;

use Magento\Framework\GraphQl\Argument\Filter\Clause\ReferenceType;

/**
 * This interface holds different clause types: string, array, boolean, etc
 */
interface ClauseInterface
{
    /**
     * Get the referenced type of the entity for the field
     *
     * @return ReferenceType
     */
    public function getReferencedType() : ReferenceType;

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName();

    /**
     * Get the clause type
     *
     * @return string
     */
    public function getClauseType();
}
