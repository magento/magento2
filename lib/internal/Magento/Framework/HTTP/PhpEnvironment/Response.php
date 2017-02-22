<?php
/**
 * Base HTTP response object
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

class Response extends \Zend\Http\PhpEnvironment\Response implements \Magento\Framework\App\Response\HttpInterface
{
    /**
     * Flag; is this response a redirect?
     * @var boolean
     */
    protected $isRedirect = false;

    /**
     * Get header value by name.
     * Returns first found header by passed name.
     * If header with specified name was not found returns false.
     *
     * @param string $name
     * @return \Zend\Http\Header\HeaderInterface|bool
     */
    public function getHeader($name)
    {
        $header = false;
        $headers = $this->getHeaders();
        if ($headers->has($name)) {
            $header = $headers->get($name);
        }
        return $header;
    }

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested.
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->send();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function appendBody($value)
    {
        $body = $this->getContent();
        $this->setContent($body . $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setBody($value)
    {
        $this->setContent($value);
        return $this;
    }

    /**
     * Clear body
     * @return $this
     */
    public function clearBody()
    {
        $this->setContent('');
        return $this;
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return $this
     */
    public function setHeader($name, $value, $replace = false)
    {
        $value = (string)$value;

        if ($replace) {
            $this->clearHeader($name);
        }

        $this->getHeaders()->addHeaderLine($name, $value);
        return $this;
    }

    /**
     * Remove header by name from header stack
     *
     * @param string $name
     * @return $this
     */
    public function clearHeader($name)
    {
        $headers = $this->getHeaders();
        if ($headers->has($name)) {
            $header = $headers->get($name);
            $headers->removeHeader($header);
        }

        return $this;
    }

    /**
     * Remove all headers
     * @return $this
     */
    public function clearHeaders()
    {
        $headers = $this->getHeaders();
        $headers->clearHeaders();

        return $this;
    }

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return $this
     */
    public function setRedirect($url, $code = 302)
    {
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        return $this;
    }

    /**
     * Set HTTP response code to use with headers
     *
     * @param int $code
     * @return $this
     */
    public function setHttpResponseCode($code)
    {
        if (!is_numeric($code) || (100 > $code) || (599 < $code)) {
            throw new \InvalidArgumentException('Invalid HTTP response code');
        }

        $this->isRedirect = (300 <= $code && 307 >= $code) ? true : false;

        $this->setStatusCode($code);
        return $this;
    }

    /**
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return $this
     */
    public function setStatusHeader($httpCode, $version = null, $phrase = null)
    {
        $version = $version === null ? $this->detectVersion() : $version;
        $phrase = $phrase === null ? $this->getReasonPhrase() : $phrase;

        $this->setVersion($version);
        $this->setHttpResponseCode($httpCode);
        $this->setReasonPhrase($phrase);

        return $this;
    }

    /**
     * Get response code
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->getStatusCode();
    }

    /**
     * Is this a redirect?
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return $this->isRedirect;
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['content', 'isRedirect', 'statusCode'];
    }
}
