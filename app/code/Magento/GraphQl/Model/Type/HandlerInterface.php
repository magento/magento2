<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\GraphQl\Type\Definition\TypeInterface;

/**
 * Define a method for generating or retrieving a type for GraphQL
 */
interface HandlerInterface
{
    /**
     * Return GraphQL configuration of type.
     *
     * @return TypeInterface
     * @throws \InvalidArgumentException No implementation found or type not implemented for interface with resolve type
     */
    public function getType();
}
