<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl NonNull
 */
class NonNull extends \GraphQL\Type\Definition\NonNull implements
    WrappedTypeInterface,
    InputTypeInterface,
    OutputTypeInterface
{

}
