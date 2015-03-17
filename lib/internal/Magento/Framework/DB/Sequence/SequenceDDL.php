<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sequence;

/**
 * Class SequenceDDL represents DDL for manage sequences
 */
class SequenceDDL
{
    /**
     * Return SQL for create sequence
     *
     * @param string $name
     * @param int $startNumber
     * @return string
     */
    public function createSequence($name, $startNumber = 1)
    {
        $format = "CREATE TABLE IF NOT EXISTS %s (
                     sequence_value MEDIUMINT NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d";

        return sprintf($format, $name, $startNumber);
    }

    /**
     * Return SQL for drop sequence
     *
     * @param $name
     * @return string
     */
    public function dropSequence($name)
    {
        $format = "DROP TABLE IF EXISTS %s";
        return sprintf($format, $name);
    }
}
