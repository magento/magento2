<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Drop element operation
 */
class DropReference implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'drop_reference';

    /**
     * @var DropElement
     */
    private $dropElement;

    /**
     * @param DropElement $dropElement
     */
    public function __construct(DropElement $dropElement)
    {
        $this->dropElement = $dropElement;
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
