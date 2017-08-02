<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;

/**
 * Class \Magento\Framework\Controller\AbstractResult
 *
 * @since 2.0.0
 */
abstract class AbstractResult implements ResultInterface
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $httpResponseCode;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $headers = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $statusHeaderCode;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $statusHeaderVersion;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $statusHeaderPhrase;

    /**
     * Set response code to result
     *
     * @param int $httpCode
     * @return $this
     * @since 2.0.0
     */
    public function setHttpResponseCode($httpCode)
    {
        $this->httpResponseCode = $httpCode;
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
     * @since 2.0.0
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->headers[] = [
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace,
        ];
        return $this;
    }

    /**
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return $this
     * @since 2.0.0
     */
    public function setStatusHeader($httpCode, $version = null, $phrase = null)
    {
        $this->statusHeaderCode = $httpCode;
        $this->statusHeaderVersion = $version;
        $this->statusHeaderPhrase = $phrase;
        return $this;
    }

    /**
     * @param HttpResponseInterface $response
     * @return $this
     * @since 2.0.0
     */
    protected function applyHttpHeaders(HttpResponseInterface $response)
    {
        if (!empty($this->httpResponseCode)) {
            $response->setHttpResponseCode($this->httpResponseCode);
        }
        if ($this->statusHeaderCode) {
            $response->setStatusHeader(
                $this->statusHeaderCode,
                $this->statusHeaderVersion,
                $this->statusHeaderPhrase
            );
        }
        if (!empty($this->headers)) {
            foreach ($this->headers as $headerData) {
                $response->setHeader($headerData['name'], $headerData['value'], $headerData['replace']);
            }
        }
        return $this;
    }
    
    /**
     * @param HttpResponseInterface $response
     * @return $this
     * @since 2.0.0
     */
    abstract protected function render(HttpResponseInterface $response);

    /**
     * Render content
     *
     * @param HttpResponseInterface|ResponseInterface $response
     * @return $this
     * @since 2.0.0
     */
    public function renderResult(ResponseInterface $response)
    {
        $this->applyHttpHeaders($response);
        return $this->render($response);
    }
}
