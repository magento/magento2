<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Controls and represents the  state of the inline translation processing.
 *
 * @api
 */
interface StateInterface
{
    /**
     * Disable inline translation
     *
     * @return void
     */
    public function disable();

    /**
     * Enable inline translation
     *
     * @return void
     */
    public function enable();

    /**
     * Check if inline translation enabled/disabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Suspend inline translation
     *
     * Store current inline translation status
     * and apply new status or disable inline translation.
     *
     * @param bool $status
     * @return void
     */
    public function suspend($status = false);

    /**
     * Disable inline translation
     *
     * Restore inline translation status
     * or apply new status.
     *
     * @param bool $status
     * @return void
     */
    public function resume($status = true);
}
