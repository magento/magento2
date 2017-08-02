<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Auth;

/**
 * Backend Auth Storage interface
 *
 * @api
 * @since 2.0.0
 */
interface StorageInterface
{
    /**
     * Perform login specific actions
     *
     * @return $this
     * @abstract
     * @api
     * @since 2.0.0
     */
    public function processLogin();

    /**
     * Perform login specific actions
     *
     * @return $this
     * @abstract
     * @api
     * @since 2.0.0
     */
    public function processLogout();

    /**
     * Check if user is logged in
     *
     * @return bool
     * @abstract
     * @api
     * @since 2.0.0
     */
    public function isLoggedIn();

    /**
     * Prolong storage lifetime
     *
     * @return void
     * @abstract
     * @api
     * @since 2.0.0
     */
    public function prolong();
}
