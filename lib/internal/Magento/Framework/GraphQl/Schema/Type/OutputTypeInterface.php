<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

use Magento\Framework\GraphQl\Schema\TypeInterface;

/**
 * Interface for GraphQl OutputType only used for output
 *
 * @api
 */
interface OutputTypeInterface extends \GraphQL\Type\Definition\OutputType, TypeInterface
{
}
