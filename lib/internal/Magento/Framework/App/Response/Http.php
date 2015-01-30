<?php
/**
 * HTTP response
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Http extends \Zend\Http\PhpEnvironment\Response implements HttpInterface
{
    /**
     * Cookie to store page vary string
     */
    const COOKIE_VARY_STRING = 'X-Magento-Vary';

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

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

    /** @var Headers */
    protected $headerManager;


    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param Headers $headerManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        Headers $headerManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->context = $context;
        $this->headerManager = $headerManager;
    }

    /**
     * Return the header container responsible for headers
     *
     * @return Headers
     */
    public function getHeaders()
    {
        if ($this->headers === null || is_string($this->headers)) {
            // this is only here for fromString lazy loading
            $this->headers = (is_string($this->headers)) ? Headers::fromString($this->headers) : $this->headerManager;
        }

        return $this->headers;
    }

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
        $this->sendVary();
        $this->send();
    }

    /**
     * @param string $value
     */
    public function appendBody($value)
    {
        $body = $this->getContent();
        $this->setContent($body . $value);
    }

    /**
     * @param string $value
     */
    public function setBody($value)
    {
        $this->setContent($value);
    }

    /**
     * Clear body
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
        $name  = $this->normalizeHeader($name);
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
     */
    public function clearHeader($name)
    {
        $name  = $this->normalizeHeader($name);
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
     */
    public function clearHeaders()
    {
        $headers = $this->getHeaders();
        $headers->clearHeaders();
    }

    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeHeader($name)
    {
        $filtered = str_replace(['-', '_'], ' ', (string)$name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    /**
     * Send Vary coookie
     *
     * @return void
     */
    public function sendVary()
    {
        $data = $this->context->getData();
        if (!empty($data)) {
            ksort($data);
            $cookieValue = sha1(serialize($data));
            $sensitiveCookMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()
                ->setPath('/');
            $this->cookieManager->setSensitiveCookie(self::COOKIE_VARY_STRING, $cookieValue, $sensitiveCookMetadata);
        } else {
            $cookieMetadata = $this->cookieMetadataFactory->createCookieMetadata()
                ->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_VARY_STRING, $cookieMetadata);
        }
    }

    /**
     * Set headers for public cache
     * Accepts the time-to-live (max-age) parameter
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setPublicHeaders($ttl)
    {
        if ($ttl < 0 || !preg_match('/^[0-9]+$/', $ttl)) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set public headers');
        }
        $this->setHeader('pragma', 'cache', true);
        $this->setHeader('cache-control', 'public, max-age=' . $ttl . ', s-maxage=' . $ttl, true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $ttl . ' seconds')), true);
    }

    /**
     * Set headers for private cache
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setPrivateHeaders($ttl)
    {
        if (!$ttl) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set private headers');
        }
        $this->setHeader('pragma', 'cache', true);
        $this->setHeader('cache-control', 'private, max-age=' . $ttl, true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $ttl . ' seconds')), true);
    }

    /**
     * Set headers for no-cache responses
     *
     * @return void
     */
    public function setNoCacheHeaders()
    {
        $this->setHeader('pragma', 'no-cache', true);
        $this->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('-1 year')), true);
    }

    /**
     * Represents an HTTP response body in JSON format by sending appropriate header
     *
     * @param string $content String in JSON format
     * @return \Magento\Framework\App\Response\Http
     */
    public function representJson($content)
    {
        $this->setHeader('Content-Type', 'application/json', true);
        return $this->setContent($content);
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
     * Register an exception with the response
     *
     * @param $e
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
        foreach ($this->_exceptions as $e) {
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
        foreach ($this->_exceptions as $e) {
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
     * @return void
     */
    public function getExceptionByCode($code)
    {
        $code = (int)$code;
        $exceptions = [];
        foreach ($this->_exceptions as $e) {
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
        return ['content', 'isRedirect', 'exceptions', 'statusCode', 'context'];
    }

    /**
     * Need to reconstruct dependencies when being de-serialized.
     *
     * @return void
     */
    public function __wakeup()
    {
        $objectManager = ObjectManager::getInstance();
        $this->cookieManager = $objectManager->create('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->cookieMetadataFactory = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
    }
}
