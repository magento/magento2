<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Definition;

/**
 * Wrapper for GraphQl StringType
 */
class StringType extends \GraphQL\Type\Definition\StringType implements InputType, OutputType
{
    /**
     * @var string
     */
    public $name = "Magento_String";
}
