<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This interface can be used for elements
 * that hold table name, like
 * constraints
 */
interface TableElementInterface
{
    /**
     * Return table with data
     *
     * @return Table
     */
    public function getTable();
}
