<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * This column represent binary type
 * We can have few binary types: blob, mediumblob, largeblog
 * Declared in SQL, like blob
 */
class Blob extends Column implements
    ElementDiffAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * By default element type is blob, but it can various: blob, mediumblob, longblob
     * @inheritdoc
     */
    protected $elementType = 'blob';

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
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'nullable' => $this->isNullable()
        ];
    }
}
