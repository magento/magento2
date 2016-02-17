<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\PhpEnvironment;

use Zend\Http\Header\MultipleHeaderInterface;
use Zend\Http\Response as HttpResponse;

/**
 * HTTP Response for current PHP environment
 */
class Response extends HttpResponse
{
    /**
     * The current used version
     * (The value will be detected on getVersion)
     *
     * @var null|string
     */
    protected $version;

    /**
     * @var bool
     */
    protected $contentSent = false;

    /**
     * Return the HTTP version for this response
     *
     * @return string
     * @see \Zend\Http\AbstractMessage::getVersion()
     */
    public function getVersion()
    {
        if (!$this->version) {
            $this->version = $this->detectVersion();
        }
        return $this->version;
    }

    /**
     * Detect the current used protocol version.
     * If detection failed it falls back to version 1.0.
     *
     * @return string
     */
    protected function detectVersion()
    {
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
            return self::VERSION_11;
        }

        return self::VERSION_10;
    }

    /**
     * @return bool
     */
    public function headersSent()
    {
        return headers_sent();
    }

    /**
     * @return bool
     */
    public function contentSent()
    {
        return $this->contentSent;
    }

    /**
     * Send HTTP headers
     *
     * @return Response
     */
    public function sendHeaders()
    {
        if ($this->headersSent()) {
            return $this;
        }

        $status  = $this->renderStatusLine();
        header($status);

        /** @var \Zend\Http\Header\HeaderInterface $header */
        foreach ($this->getHeaders() as $header) {
            if ($header instanceof MultipleHeaderInterface) {
                header($header->toString(), false);
                continue;
            }
            header($header->toString());
        }

        $this->headersSent = true;
        return $this;
    }

    /**
     * Send content
     *
     * @return Response
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
     * Send HTTP response
     *
     * @return Response
     */
    public function send()
    {
        $this->sendHeaders()
             ->sendContent();
        return $this;
    }
}
