<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Magento\Framework\Profiler\Driver\Standard\Output\Firebug;

use \Zend_Wildfire_Plugin_FirePhp as FirePHP;
use \Zend_Wildfire_Channel_HttpHeaders as HttpHeaders;

class FirebugBridge implements \Zend\Log\Writer\FirePhp\FirePhpInterface
{

    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var \Zend\Http\PhpEnvironment\Response
     */
    protected $_response;

    /**
     * Determine whether or not FirePHP is enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return FirePHP::getInstance()->getEnabled();
    }

    /**
     * Log an error message
     *
     * @param  string      $line
     * @param  string|null $label
     * @return void
     */
    public function error($line, $label = null)
    {
        FirePHP::send(unserialize($line));

        $this->_sendResponseHeaders();
    }

    /**
     * Log a warning
     *
     * @param  string      $line
     * @param  string|null $label
     * @return void
     */
    public function warn($line, $label = null)
    {
        FirePHP::send(unserialize($line));

        $this->_sendResponseHeaders();
    }

    /**
     * Log informational message
     *
     * @param  string      $line
     * @param  string|null $label
     * @return void
     */
    public function info($line, $label = null)
    {
        FirePHP::send(unserialize($line));

        $this->_sendResponseHeaders();
    }

    /**
     * Send the response headers
     *
     * @return void
     */
    protected function _sendResponseHeaders()
    {
        // setup the wildfire channel
        $firebugChannel = HttpHeaders::getInstance();
        $firebugChannel->setRequest($this->getRequest());
        $firebugChannel->setResponse($this->getResponse());

        // flush the wildfire headers into the response object
        $firebugChannel->flush();

        // send the response headers
        $firebugChannel->getResponse()->sendHeaders();
    }

    /**
     * Log a trace
     *
     * @param  string $line
     * @return void
     */
    public function trace($line)
    {
        FirePHP::send(unserialize($line));

        $this->_sendResponseHeaders();
    }

    /**
     * Log a message
     *
     * @param  string      $line
     * @param  string|null $label
     * @return void
     */
    public function log($line, $label = null)
    {
        FirePHP::send(unserialize($line));

        $this->_sendResponseHeaders();
    }

    /**
     * Request setter
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function setRequest(\Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
    }

    /**
     * Request getter
     *
     * @return \Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = new \Zend_Controller_Request_Http();
        }
        return $this->_request;
    }

    /**
     * Response setter
     *
     * @param \Zend_Controller_Response_Abstract $response
     * @return void
     */
    public function setResponse(\Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
    }

    /**
     * Response getter
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = new \Zend_Controller_Response_Http();
        }
        return $this->_response;
    }
}
