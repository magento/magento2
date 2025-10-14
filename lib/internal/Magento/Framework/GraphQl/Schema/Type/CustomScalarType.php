<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
