<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

/**
 * This interface describe script, that atomic operations with data (DML, DQL) in SQL database
 * This is wrapper for @see PatchInterface in order to define what kind of patch we have
 *
 * @api
 */
interface DataPatchInterface extends PatchInterface
{
}
