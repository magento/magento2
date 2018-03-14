<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Filter;

use Magento\Framework\GraphQl\Argument\Filter\Clause\ReferenceType;

/**
 * Class clause refers to a closure in find argument.
 *
 * Example: {sku: {eq: "product"}}
 */
class Clause
{
    /**
     * @var ReferenceType
     */
    private $referenceType;

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
     * @param ReferenceType $referenceType
     * @param string $fieldName
     * @param string $clauseType
     * @param string|array $clauseValue
     */
    public function __construct(
        ReferenceType $referenceType,
        string $fieldName,
        string $clauseType,
        $clauseValue
    ) {
        $this->referenceType = $referenceType;
        $this->fieldName = $fieldName;
        $this->clauseType = $clauseType;
        $this->clauseValue = $clauseValue;
    }

    /**
     * Get the referenced type of the entity for the field
     *
     * @return ReferenceType
     */
    public function getReferencedType()
    {
        return $this->referenceType;
    }

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get the clause type
     *
     * @return string
     */
    public function getClauseType()
    {
        return $this->clauseType;
    }

    /**
     * Get the clause value
     *
     * @return string|array
     */
    public function getClauseValue()
    {
        return $this->clauseValue;
    }
}
