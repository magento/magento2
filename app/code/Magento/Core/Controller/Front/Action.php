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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generic frontend controller
 */
namespace Magento\Core\Controller\Front;

class Action extends \Magento\Core\Controller\Varien\Action
{
    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'frontend';

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * Remember the last visited url in the session
     *
     * @return \Magento\Core\Controller\Front\Action
     */
    public function postDispatch()
    {
        parent::postDispatch();
        if (!$this->getFlag('', self::FLAG_NO_START_SESSION )) {
            $this->_objectManager->get('Magento\Core\Model\Session')
                ->setLastUrl(
                    $this->_objectManager->create('Magento\Core\Model\Url')->getUrl('*/*/*', array('_current' => true))
                );
        }
        return $this;
    }

    /**
     * Check if admin is logged in and authorized to access resource by specified ACL path
     *
     * If not authenticated, will try to do it using credentials from HTTP-request
     *
     * @param string $aclResource
     * @param \Magento\Core\Model\Logger $logger
     * @return bool
     */
    public function authenticateAndAuthorizeAdmin($aclResource, $logger)
    {
        $this->_objectManager->get('Magento\Core\Model\App')
            ->loadAreaPart(\Magento\Core\Model\App\Area::AREA_ADMINHTML, \Magento\Core\Model\App\Area::PART_CONFIG);

        /** @var $auth \Magento\Backend\Model\Auth */
        $auth = $this->_objectManager->create('Magento\Backend\Model\Auth');
        $session = $auth->getAuthStorage();

        // Try to login using HTTP-authentication
        if (!$session->isLoggedIn()) {
            list($login, $password) = $this->_objectManager->get('Magento\Core\Helper\Http')
                ->getHttpAuthCredentials($this->getRequest());
            try {
                $auth->login($login, $password);
            } catch (\Magento\Backend\Model\Auth\Exception $e) {
                $logger->logException($e);
            }
        }

        // Verify if logged in and authorized
        if (!$session->isLoggedIn()
            || !$this->_objectManager->get('Magento\AuthorizationInterface')->isAllowed($aclResource)) {
            $this->_objectManager->get('Magento\Core\Helper\Http')
                ->failHttpAuthentication($this->getResponse(), 'RSS Feeds');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        return true;
    }
}
