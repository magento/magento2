<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
 */
interface UpToDateValidatorInterface
{
    /**
     * Retrieve message, that uncover outdated component
     *
     * @return string
     */
    public function getNotUpToDateMessage() : string ;

    /**
     * Validate component whether it is up to date or not
     *
     * @return bool
     */
    public function isUpToDate() : bool ;
}
