<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\StateInterface
 *
 * @since 2.0.0
 */
interface StateInterface
{
    /**#@+
     * View modes
     */
    const MODE_ENABLED = 'enabled';

    const MODE_DISABLED = 'disabled';

    /**#@-*/

    /**#@+
     * View statuses
     */
    const STATUS_IDLE = 'idle';

    const STATUS_WORKING = 'working';

    const STATUS_SUSPENDED = 'suspended';

    /**#@-*/

    /**
     * Fill object with state data by view ID
     *
     * @param string $viewId
     * @return $this
     * @since 2.0.0
     */
    public function loadByView($viewId);

    /**
     * Save state object
     *
     * @return \Magento\Framework\Mview\View\StateInterface
     * @throws \Exception
     * @since 2.0.0
     */
    public function save();

    /**
     * Delete state object
     *
     * @return \Magento\Framework\Mview\View\StateInterface
     * @throws \Exception
     * @since 2.0.0
     */
    public function delete();

    /**
     * Get state view ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getViewId();

    /**
     * Get state mode
     *
     * @return string
     * @since 2.0.0
     */
    public function getMode();

    /**
     * Set state mode
     *
     * @param string $mode
     * @return \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    public function setMode($mode);

    /**
     * Get state status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Set state status
     *
     * @param string $status
     * @return \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    public function setStatus($status);

    /**
     * Get state version ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getVersionId();

    /**
     * Set state version ID
     *
     * @param int $versionId
     * @return \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    public function setVersionId($versionId);

    /**
     * Get state updated time
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdated();

    /**
     * Set state updated time
     *
     * @param string|int|\DateTimeInterface $updated
     * @return \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    public function setUpdated($updated);
}
