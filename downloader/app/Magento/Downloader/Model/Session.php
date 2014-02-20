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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class session
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloader\Model;

class Session extends \Magento\Downloader\Model
{
    /**
     * Session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * Init session
     *
     * @return \Magento\Downloader\Model\Session
     */
    public function start()
    {
        if (class_exists('Magento') && \Mage::isInstalled()) {
            $this->_session = \Mage::getSingleton('Magento\Backend\Model\Auth\Session');
        } else {
            session_start();
        }
        return $this;
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Set value for key
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Authentication to downloader
     */
    public function authenticate()
    {
        if (!$this->_session) {
            return $this;
        }

        if (!empty($_GET['return'])) {
            $this->set('return_url', $_GET['return']);
        }

        if ($this->_checkUserAccess()) {
            return $this;
        }

        if (!$this->controller()->isInstalled()) {
            return $this;
        }

        try {
            if ( (isset($_POST['username']) && empty($_POST['username']))
                || (isset($_POST['password']) && empty($_POST['password']))) {
                $this->addMessage('error', 'Invalid user name or password');
            }
            if (empty($_POST['username']) || empty($_POST['password'])) {
                $this->controller()->setAction('login');
                return $this;
            }
            $user = $this->_session->login($_POST['username'], $_POST['password']);
            $this->_session->refreshAcl();
            if ($this->_checkUserAccess($user)) {
                return $this;
            }
        } catch (\Exception $e) {
            $this->addMessage('error', $e->getMessage());
        }

        $this->controller()
            ->redirect(
                $this->controller()->url('loggedin'),
                true
        );
    }

    /**
     * Check is user logged in and permissions
     *
     * @param \Magento\User\Model\User|null $user
     * @return bool
     */
    protected function _checkUserAccess($user = null)
    {
        if ($user && !$user->getId()) {
            $this->addMessage('error', 'Invalid user name or password');
            $this->controller()->setAction('login');
        } elseif ($this->getUserId() || ($user && $user->getId())) {
            if (\Mage::getSingleton('Magento\AuthorizationInterface')->isAllowed('Magento_Adminhtml::all')) {
                return true;
            } else {
                $this->logout();
                $this->addMessage('error', 'Access Denied', true);
                $this->controller()->setAction('login');
            }
        }
        return false;
    }

    /**
     * Log Out
     *
     * @return \Magento\Downloader\Model\Session
     */
    public function logout()
    {
        if (!$this->_session) {
            return $this;
        }
        $this->_session->unsUser();
        return $this;
    }

    /**
     * Retrieve user
     *
     * @return mixed
     */
    public function getUserId()
    {
        if (($session = $this->_session) && ($user = $session->getUser())) {
            return $user->getId();
        }
        return false;
    }

    /**
     * Add Message
     *
     * @param string $type
     * @param string $msg
     * @param string $clear
     * @return \Magento\Downloader\Model\Session
     */
    public function addMessage($type, $msg, $clear = false)
    {
        $msgs = $this->getMessages($clear);
        $msgs[$type][] = $msg;
        $this->set('messages', $msgs);
        return $this;
    }

    /**
     * Retrieve messages from cache
     *
     * @param boolean $clear
     * @return mixed
     */
    public function getMessages($clear = true)
    {
        $msgs = $this->get('messages');
        $msgs = $msgs ? $msgs : array();
        if ($clear) {
            unset($_SESSION['messages']);
        }
        return $msgs;
    }

    /**
     * Retrieve url to adminhtml
     *
     * @return string
     */
    public function getReturnUrl()
    {
        //TODO: added logic for backend url
        return '';
    }
}
