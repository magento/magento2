<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use \GraphQL\Type\Definition\Type;

/**
 * Define a method for generating or retrieving a GraphQL's type
 */
interface HandlerInterface
{
    /**
     * Returns GraphQL configuration of type.
     *
     * @return Type
     * @throws \InvalidArgumentException No implementation found or type not implemented for interface with resolve type
     */
    public function getType();
}
