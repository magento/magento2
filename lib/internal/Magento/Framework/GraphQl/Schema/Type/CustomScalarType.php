<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for CustomScalarType
 */
class CustomScalarType extends \GraphQL\Type\Definition\CustomScalarType implements
    InputTypeInterface,
    OutputTypeInterface
{

}
