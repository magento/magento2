<?php
/**
 * Class that uses Firebug for output profiling results
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;

class Firebug extends AbstractOutput
{
    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var \Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * Start output buffering
     *
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
        ob_start();
    }

    /**
     * Display profiling results and flush output buffer
     *
     * @param Stat $stat
     * @return void
     */
    public function display(Stat $stat)
    {
        $firebugMessage = new \Zend_Wildfire_Plugin_FirePhp_TableMessage($this->_renderCaption());
        $firebugMessage->setHeader(array_keys($this->_columns));

        foreach ($this->_getTimerIds($stat) as $timerId) {
            $row = [];
            foreach ($this->_columns as $column) {
                $row[] = $this->_renderColumnValue($stat->fetch($timerId, $column), $column);
            }
            $firebugMessage->addRow($row);
        }

        \Zend_Wildfire_Plugin_FirePhp::send($firebugMessage);

        // setup the wildfire channel
        $firebugChannel = \Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $firebugChannel->setRequest($this->getRequest());
        $firebugChannel->setResponse($this->getResponse());

        // flush the wildfire headers into the response object
        $firebugChannel->flush();

        // send the response headers
        $firebugChannel->getResponse()->sendHeaders();

        ob_end_flush();
    }

    /**
     * Render timer id column value
     *
     * @param string $timerId
     * @return string
     */
    protected function _renderTimerId($timerId)
    {
        $nestingSep = preg_quote(Profiler::NESTING_SEPARATOR, '/');
        return preg_replace('/.+?' . $nestingSep . '/', '. ', $timerId);
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
     * Request getter
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
