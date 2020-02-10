<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Foreign key constraint processor.
 *
 * MySQL holds foreign keys definitions only in "CREATE TABLE" sql. So we can access them
 * only with parsing of this statement, and searching by ADD CONSTRAINT FOREIGN KEY.
 *
 * @inheritdoc
 */
class ForeignKey implements DbDefinitionProcessorInterface
{
    /**
     * Foreign key statement.
     */
    const FOREIGN_KEY_STATEMENT = 'FOREIGN KEY';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $foreignKey)
    {
        $adapter = $this->resourceConnection->getConnection(
            $foreignKey->getTable()->getResource()
        );
        $referenceTable = $this->resourceConnection->getTableName(
            $foreignKey->getReferenceTable()->getName()
        );
        //CONSTRAINT `fk_name` FOREIGN KEY (`column`) REFERENCES `table` (`column`) option
        $foreignKeySql = sprintf(
            "CONSTRAINT %s %s (%s) REFERENCES %s (%s) %s",
            $adapter->quoteIdentifier($foreignKey->getName()),
            self::FOREIGN_KEY_STATEMENT,
            $adapter->quoteIdentifier($foreignKey->getColumn()->getName()),
            $adapter->quoteIdentifier($referenceTable),
            $adapter->quoteIdentifier($foreignKey->getReferenceColumn()->getName()),
            $foreignKey->getOnDelete() ? sprintf(" ON DELETE %s", $foreignKey->getOnDelete()) : ''
        );

        return $foreignKeySql;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (!isset($data['Create Table'])) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('Can`t read foreign keys from current database')
            );
        }

        $createMySQL = $data['Create Table'];
        $ddl = [];
        $regExp  = '#,\s*CONSTRAINT\s*`([^`]*)`\s*FOREIGN KEY\s*?\(`([^`]*)`\)\s*'
            . 'REFERENCES\s*(`([^`]*)`\.)?`([^`]*)`\s*\(`([^`]*)`\)\s*'
            . '(\s*ON\s+DELETE\s*(RESTRICT|CASCADE|SET NULL|NO ACTION|SET DEFAULT))?'
            . '(\s*ON\s+UPDATE\s*(RESTRICT|CASCADE|SET NULL|NO ACTION|SET DEFAULT))?#';
        $matches = [];

        if (preg_match_all($regExp, $createMySQL, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $ddl[$match[1]] = [
                    'type' => Reference::TYPE,
                    'name' => $match[1],
                    'column' => $match[2],
                    'referenceTable' => $match[5],
                    'referenceColumn' => $match[6],
                    'onDelete' => isset($match[7]) ? $match[8] : 'NO ACTION'
                ];
            }
        }

        return $ddl;
    }
}
