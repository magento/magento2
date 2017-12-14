<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Date column
 * Declared in SQL, like DATE
 * Doesnt have any additional params
 * Is represented like: YY:MM:DD
 */
class Date extends Column implements ElementDiffAwareInterface
{
    /**
     * @inheritdoc
     */
    protected $elementType = 'date';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType
        ];
    }
}
