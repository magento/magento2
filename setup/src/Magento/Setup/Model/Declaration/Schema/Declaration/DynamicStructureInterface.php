<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Declaration;

/**
 * This interface is used for giving possibility to add dynamic structure not from XML files
 */
interface DynamicStructureInterface
{
    /**
     * Aggregate and retrieve database structure from different sources
     *
     * Output should be like here:
     * -schema (node:schema)
     *  --table (node:table)
     *   ---table_name (node should be equal to table name), e.g. store
     *      -field (node: field)
     *       ---field_name (node: should be queal to field name), e.g. store_id
     *          -identity (auto_increment)
     *          -type (integer,text,datetime,etc..) @see \Magento\Framework\DB\Adapter\Pdo\Mysql
     *          -default
     *      -index(node: filterable (B-Tree and Hash-indexes) or node:searchable (Full Index), depends on index type),
     * e.g. filterable
     *      -name(node:name) table_name
     *
     * For example,
     * schema => [
     *  table => [
     *      store_website => [
     *          field => [
     *              website_id => [
     *                 type => smallint,
     *                 unsigned => 1,
     *                 identity => 1,
     *                 name => 'website_id'
     *              ]
     *          ],
     *          name => store_website
     *      ]
     *  ]
     * ]
     *
     * @return array
     */
    public function getStructure();
}
