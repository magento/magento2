<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

/**
 * This interface says, whether patch is disabled or not
 */
interface PatchDisableInterface
{
    /**
     * If patch is disabled and it is applied - it should be removed from database
     *
     * @return bool
     */
    public function isDisabled();
}