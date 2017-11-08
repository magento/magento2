<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl;

use Magento\GraphQl\Model\GraphQl\Clause\ReferenceType;

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
     * @return ReferenceType
     */
    public function getReferencedType()
    {
        return $this->referenceType;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getClauseType()
    {
        return $this->clauseType;
    }

    /**
     * @return string|array
     */
    public function getClauseValue()
    {
        return $this->clauseValue;
    }
}
