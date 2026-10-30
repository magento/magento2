<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
