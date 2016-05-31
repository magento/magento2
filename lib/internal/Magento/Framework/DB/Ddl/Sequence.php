<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * Class Sequence represents DDL for manage sequences
 */
class Sequence
{
    /**
     * Return SQL for create sequence
     *
     * @param string $name
     * @param int $startNumber
     * @param string $columnType
     * @param bool|true $unsigned
     * @return string
     */
    public function getCreateSequenceDdl($name, $startNumber = 1, $columnType = Table::TYPE_INTEGER, $unsigned = true)
    {
        $format = "CREATE TABLE %s (
                     sequence_value %s %s NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d";

        return sprintf($format, $name, $columnType, $unsigned ? 'UNSIGNED' : '', $startNumber);
    }

    /**
     * Return SQL for drop sequence
     *
     * @param string $name
     * @return string
     */
    public function dropSequence($name)
    {
        $format = "DROP TABLE %s";
        return sprintf($format, $name);
    }
}
