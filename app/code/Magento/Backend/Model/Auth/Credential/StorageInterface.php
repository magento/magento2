<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Auth\Credential;

/**
 * Backend Auth Credential Storage interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 */
interface StorageInterface
{
    /**
     * Authenticate process.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate($username, $password);

    /**
     * Login action. Check if given username and password are valid
     *
     * @param string $username
     * @param string $password
     * @return $this
     * @abstract
     */
    public function login($username, $password);

    /**
     * Reload loaded (already authenticated) credential storage
     *
     * @return $this
     * @abstract
     */
    public function reload();

    /**
     * Check if user has available resources
     *
     * @return bool
     * @abstract
     */
    public function hasAvailableResources();

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     * @abstract
     */
    public function setHasAvailableResources($hasResources);
}
