<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Add element to table
 */
class ModifyElement implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'modify_element';

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
     * As constraints and indexes do not have modify operation, we need to substitute it
     * with remove/create operaions
     *
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        $element = $elementHistory->getNew();

        if ($element instanceof Constraint || $element instanceof Index) {
            $this->adapterMediator->dropElement($element);
            $this->adapterMediator->addElement($element);
        } else {
            /** @var Column $element */
            $this->adapterMediator->modifyColumn($element);
        }
    }
}
