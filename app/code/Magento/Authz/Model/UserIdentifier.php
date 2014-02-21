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
namespace Magento\Authz\Model;

/**
 * User identifier class. By user can be understood admin, customer, guest, web API integration.
 */
class UserIdentifier
{
    /**#@+
     * User types.
     */
    const USER_TYPE_GUEST = 'Guest';
    const USER_TYPE_CUSTOMER = 'Customer';
    const USER_TYPE_ADMIN = 'Admin';
    const USER_TYPE_INTEGRATION = 'Integration';
    /**#@-*/

    /**
     * User type (admin, customer, guest, web API integration).
     *
     * @var string
     */
    protected $_userType;

    /**
     * @var  int
     */
    protected $_userId;

    /**
     * Initialize user type and user id.
     *
     * @param UserLocatorInterface $userLocator Locator of active user.
     * @param string|null $userType
     * @param int|null $userId
     * @throws \LogicException
     */
    public function __construct(UserLocatorInterface $userLocator, $userType = null, $userId = null)
    {
        $userType = isset($userType) ? $userType : $userLocator->getUserType();
        $userId = isset($userId) ? $userId : $userLocator->getUserId();
        if ($userType == self::USER_TYPE_GUEST && $userId) {
            throw new \LogicException('Guest user must not have user ID set.');
        }
        $this->_setUserId($userId);
        $this->_setUserType($userType);
    }

    /**
     * Get user ID. Null is possible when user type is 'guest'.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Retrieve user type (admin, customer, guest, web API integration).
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->_userType;
    }

    /**
     * Set user ID.
     *
     * @param int $userId
     * @return $this
     * @throws \LogicException
     */
    protected function _setUserId($userId)
    {
        $userId = is_numeric($userId) ? (int)$userId : $userId;
        if (!is_integer($userId) || ($userId < 0)) {
            throw new \LogicException("Invalid user ID: '{$userId}'.");
        }
        $this->_userId = $userId;
        return $this;
    }

    /**
     * Set user type.
     *
     * @param string $userType
     * @return $this
     * @throws \LogicException
     */
    protected function _setUserType($userType)
    {
        $availableTypes = array(
            self::USER_TYPE_GUEST,
            self::USER_TYPE_CUSTOMER,
            self::USER_TYPE_ADMIN,
            self::USER_TYPE_INTEGRATION
        );
        if (!in_array($userType, $availableTypes)) {
            throw new \LogicException(
                "Invalid user type: '{$userType}'. Allowed types: " . implode(", ", $availableTypes)
            );
        }
        $this->_userType = $userType;
        return $this;
    }
}
