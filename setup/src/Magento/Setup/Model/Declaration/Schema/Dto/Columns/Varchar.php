<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Varchar column
 * Declared in SQL, like VARCHAR(L),
 * where L - length
 */
class Varchar extends Column implements ElementDiffAwareInterface
{
    /**
     * @inheritdoc
     */
    protected $elementType = 'varchar';

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
     * Note: default value should be string
     *
     * @return string | null
     */
    public function getDefault()
    {
        return $this->hasDefault() ? $this->structuralElementData['default'] : null;
    }

    /**
     * Length can be integer value from 0 to 1024
     *
     * @return int
     */
    public function getLength()
    {
        return $this->structuralElementData['length'];
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'nullable' => $this->isNullable(),
            'default' => $this->getDefault(),
            'length' => $this->getLength()
        ];
    }
}
