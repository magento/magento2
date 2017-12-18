<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Constraints;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * MySQL holds foreign keys definitions only in "CREATE TABLE" sql. So we can access them
 * only with parsing of this statement, and searching by ADD CONSTRAINT FOREIGN KEY
 *
 * @inheritdoc
 */
class ForeignKey implements DbSchemaProcessorInterface
{
    /**
     * @param Reference $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        /** @TODO: purge records, if the are not satisfied on delete statement */
        $foreignKeySql = sprintf(
            "FOREIGN KEY (%s) REFERENCES %s (%s)",
            $element->getColumn()->getName(),
            $element->getReferenceTable()->getName(),
            $element->getReferenceColumn()->getName()
        );

        if ($element->getOnDelete()) {
            $foreignKeySql .= sprintf("ON DELETE %s", $element->getOnDelete());
        }

        return $foreignKeySql;
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (isset($data['Create Table'])) {
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
                        'type' => 'foreign',
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

        return $data;
    }
}
