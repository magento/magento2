<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

interface ViewInterface
{
    /**
     * Return view ID
     *
     * @return string
     */
    public function getId();

    /**
     * Return view action class
     *
     * @return string
     */
    public function getActionClass();

    /**
     * Return view group
     *
     * @return string
     */
    public function getGroup();

    /**
     * Return view subscriptions
     *
     * @return array
     */
    public function getSubscriptions();

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
     * Materialize view by IDs in changelog
     *
     * @return void
     * @throws \Exception
     */
    public function update();

    /**
     * Pause view updates and set version ID to changelog's end
     *
     * @return void
     */
    public function suspend();

    /**
     * Resume view updates
     *
     * @return void
     */
    public function resume();

    /**
     * Clear precessed changelog entries
     *
     * @return void
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
     * Check whether view is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Check whether view is idle
     *
     * @return bool
     */
    public function isIdle();

    /**
     * Check whether view is working
     *
     * @return bool
     */
    public function isWorking();

    /**
     * Check whether view is paused
     *
     * @return bool
     */
    public function isSuspended();

    /**
     * Return view updated datetime
     *
     * @return string
     */
    public function getUpdated();

    /**
     * Retrieve linked changelog
     *
     * @return View\ChangelogInterface
     */
    public function getChangelog();
}
