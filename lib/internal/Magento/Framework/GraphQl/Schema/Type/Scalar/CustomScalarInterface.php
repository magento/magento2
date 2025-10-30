<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Scalar;

/**
 * Custom Scalar
 */
interface CustomScalarInterface
{
    /**
     * Serialize Value
     *
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value);

    /**
     * Parse Value
     *
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value);

    /**
     * Parse literal
     *
     * @param mixed $valueNode
     * @param array|null $variables
     * @return mixed
     */
    public function parseLiteral($valueNode, ?array $variables = null);
}
