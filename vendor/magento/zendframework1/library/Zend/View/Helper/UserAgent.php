<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Abstract */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for interacting with UserAgent instance
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_UserAgent extends Zend_View_Helper_Abstract
{
    /**
     * UserAgent instance
     *
     * @var Zend_Http_UserAgent
     */
    protected $_userAgent = null;

    /**
     * Helper method: retrieve or set UserAgent instance
     *
     * @param  null|Zend_Http_UserAgent $userAgent
     * @return Zend_Http_UserAgent
     */
    public function userAgent(Zend_Http_UserAgent $userAgent = null)
    {
        if (null !== $userAgent) {
            $this->setUserAgent($userAgent);
        }
        return $this->getUserAgent();
    }

    /**
     * Set UserAgent instance
     *
     * @param  Zend_Http_UserAgent $userAgent
     * @return Zend_View_Helper_UserAgent
     */
    public function setUserAgent(Zend_Http_UserAgent $userAgent)
    {
        $this->_userAgent = $userAgent;
        return $this;
    }

    /**
     * Retrieve UserAgent instance
     *
     * If none set, instantiates one using no configuration
     *
     * @return Zend_Http_UserAgent
     */
    public function getUserAgent()
    {
        if (null === $this->_userAgent) {
            #require_once 'Zend/Http/UserAgent.php';
            $this->setUserAgent(new Zend_Http_UserAgent());
        }
        return $this->_userAgent;
    }
}
