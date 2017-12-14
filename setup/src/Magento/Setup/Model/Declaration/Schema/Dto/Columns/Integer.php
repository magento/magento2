<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Integer column
 * Declared in SQL, like INT(11) or BIGINT(20)
 * Where digit is padding, how many zeros should be added before first non-zero digit
 */
class Integer extends Column implements ElementDiffAwareInterface
{
    /**
     * By default element type is integer, but it can various: integer, smallinteger, biginteger, tinyinteger
     * @inheritdoc
     */
    protected $elementType = 'integer';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * Column padding
     *
     * @return int
     */
    public function getPadding()
    {
        return $this->structuralElementData['padding'];
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
     * Note: default value should be int
     *
     * @return int | null
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
     * Define whether column can be autoincremented or not
     *
     * @return bool
     */
    public function isIdentity()
    {
        return isset($this->structuralElementData['identity']) && $this->structuralElementData['identity'];
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'nullable' => $this->isNullable(),
            'padding' => $this->getPadding(),
            'unsigned' => $this->isUnsigned(),
            'identity' => $this->isIdentity(),
            'default' => $this->getDefault()
        ];
    }
}
