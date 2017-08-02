<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Class \Magento\Framework\Translate\Inline\State
 *
 * @since 2.0.0
 */
class State implements StateInterface
{
    /**
     * Flag to enable/disable inine translation
     *
     * @var bool
     * @since 2.0.0
     */
    protected $isEnabled = true;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $storedStatus;

    /**
     * Disable inline translation
     *
     * @return void
     * @since 2.0.0
     */
    public function disable()
    {
        $this->isEnabled = false;
    }

    /**
     * Enable inline translation
     *
     * @return void
     * @since 2.0.0
     */
    public function enable()
    {
        $this->isEnabled = true;
    }

    /**
     * Check if inline translation enabled/disabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Suspend inline translation
     *
     * Store current inline translation status
     * and apply new status or disable inline translation.
     *
     * @param bool $status
     * @return void
     * @since 2.0.0
     */
    public function suspend($status = false)
    {
        if ($this->storedStatus === null) {
            $this->storedStatus = $this->isEnabled;
            $this->isEnabled = $status;
        }
    }

    /**
     * Disable inline translation
     *
     * Restore inline translation status
     * or apply new status.
     *
     * @param bool $status
     * @return void
     * @since 2.0.0
     */
    public function resume($status = true)
    {
        $this->isEnabled = !$status ? $status : $this->storedStatus;
        $this->storedStatus = null;
    }
}
