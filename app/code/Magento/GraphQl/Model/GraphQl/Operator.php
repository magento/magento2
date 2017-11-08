<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl;

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
     * @throws \GraphQL\Error\Error
     */
    public function __construct($value = self::AND)
    {
        if (!$value) {
            $value = self::AND;
        }
        $type = new \ReflectionClass($this);
        if (!in_array($value, $type->getConstants())) {
            throw new \GraphQL\Error\Error(sprintf('%s operator not supported', $value));
        }
        $this->value = $value;
    }

    /**
     * @return array
     */
    public static function getOperators()
    {
        $type = new \ReflectionClass(\Magento\GraphQl\Model\GraphQl\Operator::class);
        return $type->getConstants();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strtoupper($this->value);
    }
}
