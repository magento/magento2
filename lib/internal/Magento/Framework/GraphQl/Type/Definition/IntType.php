<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Definition;

/**
 * Wrapper for GraphQl IntType
 */
class IntType extends \GraphQL\Type\Definition\IntType implements InputType, OutputType
{
    /**
     * @var string
     */
    public $name = "Magento_Int";
}
