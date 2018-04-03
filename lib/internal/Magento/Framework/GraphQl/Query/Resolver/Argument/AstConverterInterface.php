<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument;

use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Connective;

interface AstConverterInterface
{
    /**
     * Get a connective filter from an AST input
     *
     * @param array $arguments
     * @return Connective
     */
    public function convert(array $arguments) : Connective;
}
