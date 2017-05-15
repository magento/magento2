<?php
/**
 * Firebug bridge
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Profiler\Driver\Standard\Output\Firebug;

class FirebugBridge implements \Zend\Log\Writer\ChromePhp\ChromePhpInterface
{

    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     * @var \Zend\Http\PhpEnvironment\Response
     */
    protected $response;

    /**
     * @var \Zend_Wildfire_Plugin_FirePhp
     */
    protected $firePhp;

    /**
     * @var \Zend_Wildfire_Channel_HttpHeaders
     */
    protected $httpHeaders;

    /**
     * FirebugBridge constructor.
     *
     * @param object $httpHeaders
     * @param object $firePhp
     */
    public function __construct(
        $httpHeaders = '\Zend_Wildfire_Channel_HttpHeaders',
        $firePhp = '\Zend_Wildfire_Plugin_FirePhp'
    ) {
        $this->firePhp = $firePhp;
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * Log an error message
     *
     * @param  string $line
     *
     * @return void
     */
    public function error($line)
    {
        $this->info($line);
    }

    /**
     * Log a warning
     *
     * @param  string $line
     *
     * @return void
     */
    public function warn($line)
    {
        $this->info($line);
    }

    /**
     * Log informational message
     *
     * @param  string $line
     *
     * @return void
     */
    public function info($line)
    {
        call_user_func([$this->firePhp, 'send'], unserialize($line));

        $this->sendResponseHeaders();
    }

    /**
     * Send the response headers
     *
     * @return void
     */
    protected function sendResponseHeaders()
    {
        // setup the wildfire channel
        $firebugChannel = call_user_func([$this->httpHeaders, 'getInstance']);
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
     *
     * @return void
     */
    public function trace($line)
    {
        $this->info($line);
    }

    /**
     * Log a message
     *
     * @param  string $line
     *
     * @return void
     */
    public function log($line)
    {
        $this->info($line);
    }

    /**
     * Request setter
     *
     * @param \Zend_Controller_Request_Abstract $request
     *
     * @return void
     */
    public function setRequest(\Zend_Controller_Request_Abstract $request)
    {
        $this->request = $request;
    }

    /**
     * Request getter
     *
     * @return \Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new \Zend_Controller_Request_Http();
        }
        return $this->request;
    }

    /**
     * Response setter
     *
     * @param \Zend_Controller_Response_Abstract $response
     *
     * @return void
     */
    public function setResponse(\Zend_Controller_Response_Abstract $response)
    {
        $this->response = $response;
    }

    /**
     * Response getter
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new \Zend_Controller_Response_Http();
        }
        return $this->response;
    }
}
