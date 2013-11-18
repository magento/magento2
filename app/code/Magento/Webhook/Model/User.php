<?php
/**
 * Representation of \Magento\PubSub\SubscriptionInterface with data from Magento database
 *
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

class User implements \Magento\Outbound\UserInterface
{
    /** @var \Magento\Webapi\Model\Acl\User  */
    private $_user;

    /**
     * Used to check that this user has proper permissions
     *
     * @var \Magento\Authorization
     */
    private $_authorization;

    /**
     * @param \Magento\Webapi\Model\Acl\User\Factory $userFactory
     * @param \Magento\Webapi\Model\Authorization\Role\Locator\Factory $roleLocatorFactory
     * @param \Magento\Webapi\Model\Authorization\Policy\Acl $aclPolicy
     * @param \Magento\Authorization\Factory $authorizationFactory
     * @param string $webapiUserId
     */
    public function __construct(
        \Magento\Webapi\Model\Acl\User\Factory $userFactory,
        \Magento\Webapi\Model\Authorization\Role\Locator\Factory $roleLocatorFactory,
        \Magento\Webapi\Model\Authorization\Policy\Acl $aclPolicy,
        \Magento\Authorization\Factory $authorizationFactory,
        $webapiUserId
    ) {
        $this->_user = $userFactory->create();
        $this->_user->load($webapiUserId);
        $roleLocator = $roleLocatorFactory->create(array(
            'data' => array('roleId' => $this->_user->getRoleId())
        ));

        $this->_authorization = $authorizationFactory->create(array(
            'aclPolicy' => $aclPolicy,
            'roleLocator' => $roleLocator
        ));
    }

    /**
     * Returns a shared secret known only by Magento and this user
     *
     * @return string A shared secret that both the user and Magento know about
     */
    public function getSharedSecret()
    {
        return $this->_user->getSecret();
    }

    /**
     * Checks whether this user has permission for the given topic
     *
     * @param string $topic Topic to check
     * @return bool True if permissions exist
     */
    public function hasPermission($topic)
    {
        return $this->_authorization->isAllowed($topic);
    }
}
