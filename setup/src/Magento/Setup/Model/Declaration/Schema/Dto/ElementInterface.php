<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This is generic interface
 *
 * It is parent interface for all various schema structural elements:
 * table, column, constaint, index
 */
interface ElementInterface
{
    /**
     * Return customer name of structural element
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieve element low level type: varchar, char, foreign key, etc..
     *
     * @return string
     */
    public function getType();

    /**
     * Retrieve high level type: column, constraint, index, table
     *
     * On high level different elements can be created or modified in different ways
     * So for each high level type of elements were created different operations
     * And in order to distinguish this types of elements we use this method
     *
     * @return string
     */
    public function getElementType();
}
