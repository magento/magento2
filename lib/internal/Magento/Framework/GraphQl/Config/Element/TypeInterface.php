<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Defines contracts for return type data as GraphQL objects.
 *
 * @api
 */
interface TypeInterface extends ConfigElementInterface
{
    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return Field[]
     */
    public function getFields(): array;
}
