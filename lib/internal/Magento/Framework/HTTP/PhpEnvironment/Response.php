<?php
/**
 * Base HTTP response object
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

class Response extends \Zend\Http\PhpEnvironment\Response implements \Magento\Framework\App\Response\HttpInterface
{
    /**
     * @var int
     */
    protected $httpResponseCode;

    /**
     * Flag; is this response a redirect?
     * @var boolean
     */
    protected $isRedirect = false;

    /**
     * Exception stack
     * @var \Exception
     */
    protected $exceptions = [];

    /**
     * Whether or not to render exceptions; off by default
     * @var boolean
     */
    protected $renderExceptions = false;

    /**
     * Get header value by name.
     * Returns first found header by passed name.
     * If header with specified name was not found returns false.
     *
     * @param string $name
     * @return \Zend\Http\Header\Interface|bool
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
        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= $e->__toString() . "\n";
            }
            echo $exceptions;
            return;
        }
        $this->send();
    }

    /**
     * @param string $value
     * @return void
     */
    public function appendBody($value)
    {
        $body = $this->getContent();
        $this->setContent($body . $value);
    }

    /**
     * @param string $value
     * @return void
     */
    public function setBody($value)
    {
        $this->setContent($value);
    }

    /**
     * Clear body
     * @return void
     */
    public function clearBody()
    {
        $this->setContent('');
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
     * @return \Magento\Framework\App\Response\Http
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
     * @return void
     */
    public function clearHeader($name)
    {
        $headers = $this->getHeaders();
        if ($headers->has($name)) {
            foreach ($headers as $header) {
                if ($header->getFieldName() == $name) {
                    $headers->removeHeader($header);
                }
            }
        }
    }

    /**
     * Remove all headers
     * @return void
     */
    public function clearHeaders()
    {
        $headers = $this->getHeaders();
        $headers->clearHeaders();
    }

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return \Magento\Framework\App\Response\Http
     */
    public function setRedirect($url, $code = 302)
    {
        $this->setHeader('Location', $url, true)
            ->setHttpResponseCode($code);

        $this->sendHeaders();

        return $this;
    }

    /**
     * Set HTTP response code to use with headers
     *
     * @param int $code
     * @return \Magento\Framework\App\Response\Http
     */
    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new \InvalidArgumentException('Invalid HTTP response code');
        }

        $this->isRedirect = (300 <= $code && 307 >= $code) ? true : false;

        $this->setStatusCode($code);
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
     * Whether or not to render exceptions (off by default)
     *
     * If called with no arguments or a null argument, returns the value of the
     * flag; otherwise, sets it and returns the current value.
     *
     * @param boolean $flag Optional
     * @return boolean
     */
    public function renderExceptions($flag = null)
    {
        if (null !== $flag) {
            $this->renderExceptions = $flag ? true : false;
        }

        return $this->renderExceptions;
    }

    /**
     * Register an exception with the response
     *
     * @param \Exception $e
     * @return $this
     */
    public function setException($e)
    {
        $this->exceptions[] = $e;
        return $this;
    }

    /**
     * Has an exception been registered with the response?
     *
     * @return boolean
     */
    public function isException()
    {
        return !empty($this->exceptions);
    }

    /**
     * Retrieve the exception stack
     *
     * @return array
     */
    public function getException()
    {
        return $this->exceptions;
    }

    /**
     * Does the response object contain an exception of a given type?
     *
     * @param  string $type
     * @return boolean
     */
    public function hasExceptionOfType($type)
    {
        foreach ($this->exceptions as $e) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does the response object contain an exception with a given message?
     *
     * @param  string $message
     * @return boolean
     */
    public function hasExceptionOfMessage($message)
    {
        foreach ($this->exceptions as $e) {
            if ($message == $e->getMessage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does the response object contain an exception with a given code?
     *
     * @param  int $code
     * @return boolean
     */
    public function hasExceptionOfCode($code)
    {
        $code = (int)$code;
        foreach ($this->exceptions as $e) {
            if ($code == $e->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve all exceptions of a given type
     *
     * @param  string $type
     * @return false|array
     */
    public function getExceptionByType($type)
    {
        $exceptions = [];
        foreach ($this->exceptions as $e) {
            if ($e instanceof $type) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * Retrieve all exceptions of a given message
     *
     * @param  string $message
     * @return false|array
     */
    public function getExceptionByMessage($message)
    {
        $exceptions = [];
        foreach ($this->exceptions as $e) {
            if ($message == $e->getMessage()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * Retrieve all exceptions of a given code
     *
     * @param mixed $code
     * @return false|array
     */
    public function getExceptionByCode($code)
    {
        $code = (int)$code;
        $exceptions = [];
        foreach ($this->exceptions as $e) {
            if ($code == $e->getCode()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['content', 'isRedirect', 'exceptions', 'statusCode'];
    }
}
