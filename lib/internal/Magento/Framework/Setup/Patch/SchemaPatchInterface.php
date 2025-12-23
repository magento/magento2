<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

/**
 * This interface describe script, that atomic operations with schema (DDL) in SQL database
 * This is wrapper for @see PatchInterface in order to define what kind of patch we have
 *
 * @api
 */
interface SchemaPatchInterface extends PatchInterface
{
}
