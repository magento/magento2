<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ViewInterface
 *
 * @since 2.0.0
 */
interface ViewInterface
{
    /**
     * Return view ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getId();

    /**
     * Return view action class
     *
     * @return string
     * @since 2.0.0
     */
    public function getActionClass();

    /**
     * Return view group
     *
     * @return string
     * @since 2.0.0
     */
    public function getGroup();

    /**
     * Return view subscriptions
     *
     * @return array
     * @since 2.0.0
     */
    public function getSubscriptions();

    /**
     * Fill view data from config
     *
     * @param string $viewId
     * @return ViewInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function load($viewId);

    /**
     * Create subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     * @since 2.0.0
     */
    public function subscribe();

    /**
     * Remove subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     * @since 2.0.0
     */
    public function unsubscribe();

    /**
     * Materialize view by IDs in changelog
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function update();

    /**
     * Pause view updates and set version ID to changelog's end
     *
     * @return void
     * @since 2.0.0
     */
    public function suspend();

    /**
     * Resume view updates
     *
     * @return void
     * @since 2.0.0
     */
    public function resume();

    /**
     * Clear precessed changelog entries
     *
     * @return void
     * @since 2.0.0
     */
    public function clearChangelog();

    /**
     * Return related state object
     *
     * @return View\StateInterface
     * @since 2.0.0
     */
    public function getState();

    /**
     * Set view state object
     *
     * @param View\StateInterface $state
     * @return ViewInterface
     * @since 2.0.0
     */
    public function setState(View\StateInterface $state);

    /**
     * Check whether view is enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled();

    /**
     * Check whether view is idle
     *
     * @return bool
     * @since 2.0.0
     */
    public function isIdle();

    /**
     * Check whether view is working
     *
     * @return bool
     * @since 2.0.0
     */
    public function isWorking();

    /**
     * Check whether view is paused
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSuspended();

    /**
     * Return view updated datetime
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdated();

    /**
     * Retrieve linked changelog
     *
     * @return View\ChangelogInterface
     * @since 2.0.0
     */
    public function getChangelog();
}
