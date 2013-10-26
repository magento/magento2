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
 * Core Http Helper
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Helper;

class Http extends \Magento\Core\Helper\AbstractHelper
{
    const XML_NODE_REMOTE_ADDR_HEADERS  = 'global/remote_addr_headers';

    /**
     * Remote address cache
     *
     * @var string
     */
    protected $_remoteAddr;

    /**
     * Core string
     *
     * @var \Magento\Core\Helper\String
     */
    protected $_coreString = null;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * @param \Magento\Core\Helper\String $coreString
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Config $coreConfig
     */
    public function __construct(
        \Magento\Core\Helper\String $coreString,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Config $coreConfig
    ) {
        $this->_coreString = $coreString;
        $this->_coreConfig = $coreConfig;
        parent::__construct($context);
    }

    /**
     * Extract "login" and "password" credentials from HTTP-request
     *
     * Returns plain array with 2 items: login and password respectively
     *
     * @param \Magento\App\RequestInterface $request
     * @return array
     */
    public function getHttpAuthCredentials(\Magento\App\RequestInterface $request)
    {
        $server = $request->getServer();
        $user = '';
        $pass = '';

        if (empty($server['HTTP_AUTHORIZATION'])) {
            foreach ($server as $k => $v) {
                if (substr($k, -18) === 'HTTP_AUTHORIZATION' && !empty($v)) {
                    $server['HTTP_AUTHORIZATION'] = $v;
                    break;
                }
            }
        }

        if (isset($server['PHP_AUTH_USER']) && isset($server['PHP_AUTH_PW'])) {
            $user = $server['PHP_AUTH_USER'];
            $pass = $server['PHP_AUTH_PW'];
        }
        /**
         * IIS Note: for HTTP authentication to work with IIS,
         * the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
         */
        elseif (!empty($server['HTTP_AUTHORIZATION'])) {
            $auth = $server['HTTP_AUTHORIZATION'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        }
        elseif (!empty($server['Authorization'])) {
            $auth = $server['Authorization'];
            list($user, $pass) = explode(':', base64_decode(substr($auth, strpos($auth, " ") + 1)));
        }

        return array($user, $pass);
    }

    /**
     * Set "auth failed" headers to the specified response object
     *
     * @param \Magento\App\ResponseInterface $response
     * @param string $realm
     */
    public function failHttpAuthentication(\Magento\App\ResponseInterface $response, $realm)
    {
        $response->setHeader('HTTP/1.1', '401 Unauthorized')
            ->setHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"')
            ->setBody('<h1>401 Unauthorized</h1>')
        ;
    }

    /**
     * Retrieve Remote Addresses Additional check Headers
     *
     * @return array
     */
    public function getRemoteAddrHeaders()
    {
        $headers = array();
        $element = $this->_coreConfig->getNode(self::XML_NODE_REMOTE_ADDR_HEADERS);
        if ($element instanceof \Magento\Core\Model\Config\Element) {
            foreach ($element->children() as $node) {
                $headers[] = (string)$node;
            }
        }

        return $headers;
    }

    /**
     * Retrieve Client Remote Address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getRemoteAddr($ipToLong = false)
    {
        if (is_null($this->_remoteAddr)) {
            $headers = $this->getRemoteAddrHeaders();
            foreach ($headers as $var) {
                if ($this->_getRequest()->getServer($var, false)) {
                    $this->_remoteAddr = $_SERVER[$var];
                    break;
                }
            }

            if (!$this->_remoteAddr) {
                $this->_remoteAddr = $this->_getRequest()->getServer('REMOTE_ADDR');
            }
        }

        if (!$this->_remoteAddr) {
            return false;
        }

        return $ipToLong ? ip2long($this->_remoteAddr) : $this->_remoteAddr;
    }

    /**
     * Retrieve Server IP address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getServerAddr($ipToLong = false)
    {
        $address = $this->_getRequest()->getServer('SERVER_ADDR');
        if (!$address) {
            return false;
        }
        return $ipToLong ? ip2long($address) : $address;
    }

    /**
     * Retrieve HTTP "clean" value
     *
     * @param string $var
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    protected function _getHttpCleanValue($var, $clean = true)
    {
        $value = $this->_getRequest()->getServer($var, '');
        if ($clean) {
            $value = $this->_coreString->cleanString($value);
        }

        return $value;
    }

    /**
     * Retrieve HTTP HOST
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpHost($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_HOST', $clean);
    }

    /**
     * Retrieve HTTP USER AGENT
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpUserAgent($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_USER_AGENT', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT LANGUAGE
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptLanguage($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_LANGUAGE', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT CHARSET
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptCharset($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_CHARSET', $clean);
    }

    /**
     * Retrieve HTTP REFERER
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpReferer($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_REFERER', $clean);
    }

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getRequestUri($clean = false)
    {
        $uri = $this->_getRequest()->getRequestUri();
        if ($clean) {
            $uri = $this->_coreString->cleanString($uri);
        }
        return $uri;
    }

    /**
     * Validate IP address
     *
     * @param string $address
     * @return boolean
     */
    public function validateIpAddr($address)
    {
        return preg_match('#^(1?\d{1,2}|2([0-4]\d|5[0-5]))(\.(1?\d{1,2}|2([0-4]\d|5[0-5]))){3}$#', $address);
    }
}
