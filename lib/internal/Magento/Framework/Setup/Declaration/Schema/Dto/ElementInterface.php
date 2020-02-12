<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Generic DTO Element interface.
 *
 * Is parent interface for all various schema structural elements:
 * table, column, constraint, index.
 * @api
 * @since 102.0.0
 */
interface ElementInterface
{
    /**
     * Return name of structural element.
     *
     * @return string
     * @since 102.0.0
     */
    public function getName();

    /**
     * Retrieve element low level type: varchar, char, foreign key, etc..
     *
     * @return string
     * @since 102.0.0
     */
    public function getType();

    /**
     * Retrieve high level type: column, constraint, index, table.
     *
     * On high level different elements can be created or modified in different ways.
     * So for each high level type of elements were created different operations.
     * And in order to distinguish this types of elements we use this method.
     *
     * @return string
     * @since 102.0.0
     */
    public function getElementType();
}
