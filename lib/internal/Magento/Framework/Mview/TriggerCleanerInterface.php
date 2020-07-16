<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview;

/**
 * Service for processing of DB triggers
 */
interface TriggerCleanerInterface
{
    /**
     * Remove the outdated trigger from the system
     *
     * @return bool
     */
    public function unsubscribe(): bool;
}
