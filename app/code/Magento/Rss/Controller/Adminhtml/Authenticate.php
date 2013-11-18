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
 * @category    Magento
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * RSS Controller with HTTP Basic authentication
 */
namespace Magento\Rss\Controller\Adminhtml;

class Authenticate extends \Magento\Backend\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\Controller\Context $context
     */
    public function __construct(\Magento\Backend\Controller\Context $context)
    {
        $this->_logger = $context->getLogger();
        parent::__construct($context);
        $this->_objectManager->get('Magento\Backend\Model\Url')->turnOffSecretKey();
    }

    /**
     * Return required ACL resource for current action
     * @return string
     */
    protected function _getActionAclResource()
    {
        return 'Magento_Rss::rss';
    }

    /**
     * Replace standard admin login form with HTTP Basic authentication
     * @return bool|\Magento\Backend\Controller\AbstractAction
     */
    protected function _initAuthentication()
    {
        $aclResource = $this->_getActionAclResource();
        if (!$aclResource) {
            return parent::_initAuthentication();
        }

        /** @var $auth \Magento\Backend\Model\Auth */
        $auth = $this->_objectManager->create('Magento\Backend\Model\Auth');
        $session = $auth->getAuthStorage();

        // Try to login using HTTP-authentication
        if (!$session->isLoggedIn()) {
            list($login, $password) = $this->_objectManager->get('Magento\HTTP\Authentication')->getCredentials();
            try {
                $auth->login($login, $password);
            } catch (\Magento\Backend\Model\Auth\Exception $e) {
                $this->_logger->logException($e);
            }
        }

        // Verify if logged in and authorized
        if (!$session->isLoggedIn()
            || !$this->_objectManager->get('Magento\AuthorizationInterface')->isAllowed($aclResource)
        ) {
            $this->_objectManager->get('Magento\HTTP\Authentication')->setAuthenticationFailed('RSS Feeds');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        return true;
    }
}
