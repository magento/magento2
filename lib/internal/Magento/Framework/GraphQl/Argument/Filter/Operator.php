<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Filter;

use Magento\Framework\Phrase;

/**
 * Operator is the part in the find argument that does logic branching.
 *
 * Example: {"and": { "or": {} } }
 */
class Operator
{
    /**
     * Default Operator
     */
    const __DEFAULT = self::AND;

    /**
     * OR operator
     */
    const OR = 'or';

    /**
     * AND operator
     */
    const AND = 'and';

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    public function __construct($value = self::AND)
    {
        if (!$value) {
            $value = self::AND;
        }
        $type = new \ReflectionClass($this);
        if (!in_array($value, $type->getConstants())) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
                new Phrase('%1 operator not supported', [$value])
            );
        }
        $this->value = $value;
    }

    /**
     * Get the operators defined by this class as constants
     *
     * @return array
     */
    public static function getOperators()
    {
        $type = new \ReflectionClass(Operator::class);
        return $type->getConstants();
    }

    /*
     * Convert operator to string
     *
     * @return string
     */
    public function __toString()
    {
        return strtoupper($this->value);
    }
}
