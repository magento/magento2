<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\ResponseSender;

use Zend\EventManager\Event;
use Zend\Stdlib\ResponseInterface;

class SendResponseEvent extends Event
{
    /**#@+
     * Send response events triggered by eventmanager
     */
    const EVENT_SEND_RESPONSE = 'sendResponse';
    /**#@-*/

    /**
     * @var string Event name
     */
    protected $name = 'sendResponse';

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $headersSent = array();

    /**
     * @var array
     */
    protected $contentSent = array();

    /**
     * @param ResponseInterface $response
     * @return SendResponseEvent
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->setParam('response', $response);
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set content sent for current response
     *
     * @return SendResponseEvent
     */
    public function setContentSent()
    {
        $response = $this->getResponse();
        $contentSent = $this->getParam('contentSent', array());
        $contentSent[spl_object_hash($response)] = true;
        $this->setParam('contentSent', $contentSent);
        $this->contentSent[spl_object_hash($response)] = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function contentSent()
    {
        $response = $this->getResponse();
        if (isset($this->contentSent[spl_object_hash($response)])) {
            return true;
        }
        return false;
    }

    /**
     * Set headers sent for current response object
     *
     * @return SendResponseEvent
     */
    public function setHeadersSent()
    {
        $response = $this->getResponse();
        $headersSent = $this->getParam('headersSent', array());
        $headersSent[spl_object_hash($response)] = true;
        $this->setParam('headersSent', $headersSent);
        $this->headersSent[spl_object_hash($response)] = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function headersSent()
    {
        $response = $this->getResponse();
        if (isset($this->headersSent[spl_object_hash($response)])) {
            return true;
        }
        return false;
    }
}
