<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument;

use Magento\Framework\GraphQl\Argument\Filter\Connective;

interface AstConverterInterface
{
    /**
     * Get a connective filter from an AST input
     *
     * @param string $entityType
     * @param array $arguments
     * @return Connective
     */
    public function convert(string $entityType, $arguments);
}