<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl IntType
 */
class IntType extends \GraphQL\Type\Definition\IntType implements InputTypeInterface, OutputTypeInterface
{
    /**
     * @var string
     */
    public string $name = "Magento_Int";
}
