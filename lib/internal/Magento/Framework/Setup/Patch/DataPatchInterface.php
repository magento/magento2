<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Patch;

/**
 * This interface describe script, that atomic operations with data (DML, DQL) in SQL database
 * This is wrapper for @see PatchInterface in order to define what kind of patch we have
 */
interface DataPatchInterface extends PatchInterface
{
}
