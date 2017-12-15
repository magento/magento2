<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Decimal column
 * Declared in SQL, like FLOAT(S, P) or DECIMAL(S, P)
 * Where S - is scale, P - is precission
 */
class Decimal extends Column implements
    ElementDiffAwareInterface,
    ColumnUnsignedAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * By default element type is decimal, but it can various: decimal, float, double
     * @inheritdoc
     */
    protected $elementType = 'decimal';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * Column precission
     *
     * @return int
     */
    public function getPrecission()
    {
        return $this->structuralElementData['precission'];
    }

    /**
     * Column scale
     *
     * @return int
     */
    public function getScale()
    {
        return $this->structuralElementData['scale'];
    }

    /**
     * Check whether column can be nullable
     *
     * @return bool
     */
    public function isNullable()
    {
        return isset($this->structuralElementData['nullable']) && $this->structuralElementData['nullable'];
    }

    /**
     * Check whether column has default value
     *
     * @return bool
     */
    public function hasDefault()
    {
        return isset($this->structuralElementData['default']);
    }

    /**
     * Return default value
     * Note: default value should be float
     *
     * @return float | null
     */
    public function getDefault()
    {
        return $this->hasDefault() ? $this->structuralElementData['default'] : null;
    }

    /**
     * Check whether element is unsigned or not
     *
     * @return bool
     */
    public function isUnsigned()
    {
        return isset($this->structuralElementData['unsigned']) && $this->structuralElementData['unsigned'];
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'nullable' => $this->isNullable(),
            'precission' => $this->getPrecission(),
            'scale' => $this->getScale(),
            'unsigned' => $this->isUnsigned(),
            'default' => $this->getDefault()
        ];
    }
}
