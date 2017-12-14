<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Timestamp column
 * Declared in SQL, like Timestamp
 * Has 2 additional params: default and on_update
 */
class Timestamp extends Column implements ElementDiffAwareInterface
{
    /**
     * By default element type is timesstamp. But can be also datetime
     * @inheritdoc
     */
    protected $elementType = 'timestamp';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * Return default value
     * Note: default value should be float
     *
     * @return int | null
     */
    public function getDefault()
    {
        return $this->structuralElementData['default'];
    }

    /**
     * on_update is optional param
     *
     * @return bool
     */
    public function hasOnUpdate()
    {
        return isset($this->structuralElementData['on_update']);
    }

    /**
     * Retrieve on_update param
     *
     * @return string
     */
    public function getOnUpdate()
    {
        return $this->hasOnUpdate() ? $this->structuralElementData['on_update'] : null;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'default' => $this->getDefault(),
            'onUpdate' => $this->getOnUpdate()
        ];
    }
}
