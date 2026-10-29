<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Drop foreign key operation.
 */
class DropReference implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'drop_reference';

    /**
     * @var DropElement
     */
    private $dropElement;

    /**
     * Constructor.
     *
     * @param DropElement $dropElement
     */
    public function __construct(DropElement $dropElement)
    {
        $this->dropElement = $dropElement;
    }

    /**
     * @inheritdoc
     */
    public function isOperationDestructive()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        return $this->dropElement->doOperation($elementHistory);
    }
}
