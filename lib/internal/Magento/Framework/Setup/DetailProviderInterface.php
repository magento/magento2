<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup;

/**
 * Allows to check whether specific component of database is up to date.
 *
 * New way of interaction with database implies that there can be components like:
 *  - Declarative Schema
 *  - Data Patches
 *  - Schema Patches
 *  - In Future (maybe): triggers, stored procedures, etc
 *
 * Old way implies, that each module has 2 components: data and schema
 *
 * @api
 */
interface DetailProviderInterface
{
    /**
     * Retrieve detailed information about validator state and differences found
     *
     * @return array
     */
    public function getDetails() : array;
}
