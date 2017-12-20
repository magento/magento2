<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Drop element operation
 */
class DropElement implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'drop_element';

    /**
     * @var AdapterMediator
     */
    private $adapterMediator;

    /**
     * @param AdapterMediator $adapterMediator
     */
    public function __construct(AdapterMediator $adapterMediator)
    {
        $this->adapterMediator = $adapterMediator;
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
        $this->adapterMediator->dropElement($elementHistory->getNew());
    }
}
