<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Boolean column
 * Declared in SQL, like TINYINT(1) or BOOL or BOOLEAN. Is just alias for integer or binary type.
 */
class Boolean extends Column implements
    ElementDiffAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * @inheritdoc
     */
    protected $elementType = 'boolean';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

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
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'nullable' => $this->isNullable(),
            'default' => $this->getDefault()
        ];
    }
}
