<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Text column
 * Declared in SQL, like: TEXT, MEDIUMTEXT, LONGTEXT
 */
class Text extends Column implements ElementDiffAwareInterface
{
    /**
     * By default element type is text, but it can various: text, mediumtext, longtext
     * @inheritdoc
     */
    protected $elementType = 'text';

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
            'nullable' => $this->isNullable(),
        ];
    }
}
