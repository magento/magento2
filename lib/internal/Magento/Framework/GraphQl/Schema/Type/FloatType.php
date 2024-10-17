<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl FloatType
 */
class FloatType extends \GraphQL\Type\Definition\FloatType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public string $name = "Magento_Float";
}
