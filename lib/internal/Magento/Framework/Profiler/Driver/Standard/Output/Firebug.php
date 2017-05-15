<?php
/**
 * Class that uses Firebug for output profiling results
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\Driver\Standard\Output\Firebug\FirebugBridge;
use Zend\Log\Writer\ChromePhp;

class Firebug extends AbstractOutput
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
     * Display profiling results
     *
     * @param Stat $stat
     * @return void
     */
    public function display(Stat $stat)
    {
        $writer = new ChromePhp(new FirebugBridge());
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $firebugMessage = new \Zend_Wildfire_Plugin_FirePhp_TableMessage($this->_renderCaption());
        $firebugMessage->setHeader(array_keys($this->_columns));

        foreach ($this->_getTimerIds($stat) as $timerId) {
            $row = [];
            foreach ($this->_columns as $column) {
                $row[] = $this->_renderColumnValue($stat->fetch($timerId, $column), $column);
            }
            $firebugMessage->addRow($row);
        }

        $logger->info(serialize($firebugMessage));

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
     * @deprecated
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
     * @deprecated
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
     * @deprecated
     * @param \Magento\Framework\HTTP\PhpEnvironment\Response $response
     * @return void
     */
    public function setResponse(\Magento\Framework\HTTP\PhpEnvironment\Response $response)
    {
        $this->_response = $response;
    }

    /**
     * Request getter
     *
     * @deprecated
     * @return \Magento\Framework\HTTP\PhpEnvironment\Response
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = new \Magento\Framework\HTTP\PhpEnvironment\Response();
        }
        return $this->_response;
    }
}
