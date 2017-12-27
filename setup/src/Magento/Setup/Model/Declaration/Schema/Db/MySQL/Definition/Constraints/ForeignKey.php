<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * MySQL holds foreign keys definitions only in "CREATE TABLE" sql. So we can access them
 * only with parsing of this statement, and searching by ADD CONSTRAINT FOREIGN KEY
 *
 * @inheritdoc
 */
class ForeignKey implements DbDefinitionProcessorInterface
{
    /**
     * Usually used in MySQL requests
     */
    const FOREIGN_KEY_NAME = 'FOREIGN KEY';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Reference $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        $adapter = $this->resourceConnection->getConnection(
            $element->getTable()->getResource()
        );
        /** @TODO: purge records, if the are not satisfied on delete statement */
        $foreignKeySql = sprintf(
            "%s (%s) REFERENCES %s (%s)",
            self::FOREIGN_KEY_NAME,
            $adapter->quoteIdentifier($element->getColumn()->getName()),
            $adapter->quoteIdentifier($element->getReferenceTable()->getName()),
            $adapter->quoteIdentifier($element->getReferenceColumn()->getName())
        );

        if ($element->getOnDelete()) {
            $foreignKeySql .= sprintf(" ON DELETE %s", $element->getOnDelete());
        }

        return $foreignKeySql;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $createMySQL = $data['Create Table'];
        $ddl = [];
        $regExp  = '#,\s+CONSTRAINT `([^`]*)` FOREIGN KEY ?\(`([^`]*)`\) '
            . 'REFERENCES (`([^`]*)`\.)?`([^`]*)` \(`([^`]*)`\)'
            . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?'
            . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?#';
        $matches = [];

        if (preg_match_all($regExp, $createMySQL, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $ddl[$match[1]] = [
                    'type' => Reference::TYPE,
                    'name' => $match[1],
                    'column' => $match[2],
                    'referenceTable' => $match[5],
                    'referenceColumn' => $match[6],
                    'onDelete' => isset($match[7]) ? $match[8] : ''
                ];
            }
        }

        return $ddl;
    }
}
