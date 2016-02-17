<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console;

use Zend\Stdlib\Message;
use Zend\Stdlib\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    /**
     * @var bool
     */
    protected $contentSent = false;

    /**
     * Check if content was sent
     *
     * @return bool
     * @deprecated
     */
    public function contentSent()
    {
        return $this->contentSent;
    }

    /**
     * Set the error level that will be returned to shell.
     *
     * @param int   $errorLevel
     * @return Response
     */
    public function setErrorLevel($errorLevel)
    {
        if (is_string($errorLevel) && !ctype_digit($errorLevel)) {
            return $this;
        }

        $this->setMetadata('errorLevel', $errorLevel);
        return $this;
    }

    /**
     * Get response error level that will be returned to shell.
     *
     * @return int|0
     */
    public function getErrorLevel()
    {
        return $this->getMetadata('errorLevel', 0);
    }

    /**
     * Send content
     *
     * @return Response
     * @deprecated
     */
    public function sendContent()
    {
        if ($this->contentSent()) {
            return $this;
        }
        echo $this->getContent();
        $this->contentSent = true;
        return $this;
    }

    /**
     * @deprecated
     */
    public function send()
    {
        $this->sendContent();
        $errorLevel = (int) $this->getMetadata('errorLevel', 0);
        exit($errorLevel);
    }
}
