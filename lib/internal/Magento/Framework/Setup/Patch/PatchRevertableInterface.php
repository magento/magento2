<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

/**
 * Revertable means, that patch can be reverted
 *
 * All patches (@see PatchInterface) that implement this interfaces should have next values:
 * - do not use application layer: like Serilizer, Collections, etc
 * - use only some DML operations: INSERT, UPDATE
 * - DELETE DML operation is prohibited, because it can cause triggering foreign keys constraints
 * - all schema patches are not revertable
 *
 * @api
 */
interface PatchRevertableInterface
{
    /**
     * Rollback all changes, done by this patch
     *
     * @return void
     */
    public function revert();
}
