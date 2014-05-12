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
namespace Magento\Backend\Model\Auth\Credential;

/**
 * Backend Auth Credential Storage interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
