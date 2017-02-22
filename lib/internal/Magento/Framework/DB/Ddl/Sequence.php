<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @return string
     */
    public function getCreateSequenceDdl($name, $startNumber = 1)
    {
        $format = "CREATE TABLE %s (
                     sequence_value %s UNSIGNED NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d";

        return sprintf($format, $name, Table::TYPE_INTEGER, $startNumber);
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
