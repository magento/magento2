<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;

abstract class AbstractResult implements ResultInterface
{
    /**
     * @var int
     */
    protected $httpResponseCode;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $statusHeaderCode;

    /**
     * @var string
     */
    protected $statusHeaderVersion;

    /**
     * @var string
     */
    protected $statusHeaderPhrase;

    /**
     * Set response code to result
     *
     * @param int $httpCode
     * @return $this
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
     */
    abstract protected function render(HttpResponseInterface $response);

    /**
     * Render content
     *
     * @param HttpResponseInterface|ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        $this->applyHttpHeaders($response);
        return $this->render($response);
    }
}
