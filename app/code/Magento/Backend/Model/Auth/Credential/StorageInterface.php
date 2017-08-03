<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Auth\Credential;

/**
 * Backend Auth Credential Storage interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
interface StorageInterface
{
    /**
     * Authenticate process.
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @since 2.0.0
     */
    public function authenticate($username, $password);

    /**
     * Login action. Check if given username and password are valid
     *
     * @param string $username
     * @param string $password
     * @return $this
     * @abstract
     * @since 2.0.0
     */
    public function login($username, $password);

    /**
     * Reload loaded (already authenticated) credential storage
     *
     * @return $this
     * @abstract
     * @since 2.0.0
     */
    public function reload();

    /**
     * Check if user has available resources
     *
     * @return bool
     * @abstract
     * @since 2.0.0
     */
    public function hasAvailableResources();

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     * @abstract
     * @since 2.0.0
     */
    public function setHasAvailableResources($hasResources);
}
