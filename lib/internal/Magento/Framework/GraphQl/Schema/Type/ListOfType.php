<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl ListOfType
 */
class ListOfType extends \GraphQL\Type\Definition\ListOfType implements
    WrappedTypeInterface,
    InputTypeInterface,
    OutputTypeInterface
{

}
