<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Filter;

/**
 * Class clause refers to a closure in find argument.
 *
 * Example: {sku: {eq: "product"}}
 */
class Clause
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $clauseType;

    /**
     * @var string
     */
    private $clauseValue;

    /**
     * @param string $fieldName
     * @param string $clauseType
     * @param string|array $clauseValue
     */
    public function __construct(
        string $fieldName,
        string $clauseType,
        $clauseValue
    ) {
        $this->fieldName = $fieldName;
        $this->clauseType = $clauseType;
        $this->clauseValue = $clauseValue;
    }

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    /**
     * Get the clause type
     *
     * @return string
     */
    public function getClauseType(): string
    {
        return $this->clauseType;
    }

    /**
     * Get the clause value
     *
     * @return string
     */
    public function getClauseValue()
    {
        return $this->clauseValue;
    }
}
