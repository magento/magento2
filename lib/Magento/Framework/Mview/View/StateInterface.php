<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *   
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Mview\View;

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
     */
    public function loadByView($viewId);

    /**
     * Save state object
     *
     * @return \Magento\Framework\Mview\View\StateInterface
     * @throws \Exception
     */
    public function save();

    /**
     * Delete state object
     *
     * @return \Magento\Framework\Mview\View\StateInterface
     * @throws \Exception
     */
    public function delete();

    /**
     * Get state view ID
     *
     * @return string
     */
    public function getViewId();

    /**
     * Get state mode
     *
     * @return string
     */
    public function getMode();

    /**
     * Set state mode
     *
     * @param string $mode
     * @return \Magento\Framework\Mview\View\StateInterface
     */
    public function setMode($mode);

    /**
     * Get state status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set state status
     *
     * @param string $status
     * @return \Magento\Framework\Mview\View\StateInterface
     */
    public function setStatus($status);

    /**
     * Get state version ID
     *
     * @return string
     */
    public function getVersionId();

    /**
     * Set state version ID
     *
     * @param int $versionId
     * @return \Magento\Framework\Mview\View\StateInterface
     */
    public function setVersionId($versionId);

    /**
     * Get state updated time
     *
     * @return string
     */
    public function getUpdated();

    /**
     * Set state updated time
     *
     * @param string|int|\Magento\Framework\Stdlib\DateTime\DateInterface $updated
     * @return \Magento\Framework\Mview\View\StateInterface
     */
    public function setUpdated($updated);
}
