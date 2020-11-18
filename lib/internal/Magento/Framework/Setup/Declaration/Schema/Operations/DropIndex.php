<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\AdapterMediator;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\TableElementInterface;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Drop index operation.
 *
 * Drops index.
 */
class DropIndex implements OperationInterface
{
    /**
     * Operation name.
     */
    public const OPERATION_NAME = 'drop_index';

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * Constructor.
     *
     * @param DbSchemaWriterInterface $dbSchemaWriter
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter
    ) {
        $this->dbSchemaWriter = $dbSchemaWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function isOperationDestructive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /**
         * @var TableElementInterface | ElementInterface $element
         */
        $element = $elementHistory->getNew();

        return [
            $this->dbSchemaWriter->dropElement(
                $element->getTable()->getResource(),
                $element->getName(),
                $element->getTable()->getName(),
                $element->getType()
            )
        ];
    }
}
