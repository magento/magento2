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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Mview;

interface ViewInterface
{
    /**
     * Fill view data from config
     *
     * @param string $viewId
     * @return ViewInterface
     * @throws \InvalidArgumentException
     */
    public function load($viewId);

    /**
     * Create subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     */
    public function subscribe();

    /**
     * Remove subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     */
    public function unsubscribe();

    /**
     * @return mixed
     */
    public function update();

    /**
     * Clear precessed changelog entries
     */
    public function clearChangelog();

    /**
     * Return related state object
     *
     * @return View\StateInterface
     */
    public function getState();

    /**
     * Set view state object
     *
     * @param View\StateInterface $state
     * @return ViewInterface
     */
    public function setState(View\StateInterface $state);

    /**
     * Return view mode
     *
     * @return string
     */
    public function getMode();

    /**
     * Return view status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Retrieve linked changelog
     *
     * @return View\ChangelogInterface
     */
    public function getChangelog();
}
