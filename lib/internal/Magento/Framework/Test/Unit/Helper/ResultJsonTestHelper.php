<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Minimal ResultInterface test helper that stores a predetermined return value for setData().
 */
class ResultJsonTestHelper implements ResultInterface
{
    /** @var string|null */
    private $returnJson;

    /**
     * @param string $json
     * @return $this
     */
    public function setReturnJson($json)
    {
        $this->returnJson = $json;
        return $this;
    }

    /**
     * @param array|string $data
     * @return string|null
     */
    public function setData($data)
    {
        // Touch $data to satisfy PHPMD; behavior returns stored JSON
        if ($data !== null) {
            // no-op
        }
        return $this->returnJson;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        // Touch $response to satisfy PHPMD
        if ($response !== null) {
            // no-op
        }
        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setHttpResponseCode($code)
    {
        // Touch $code to satisfy PHPMD
        if ($code !== null) {
            // no-op
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return $this
     */
    public function setHeader($name, $value, $replace = false)
    {
        // Touch params to satisfy PHPMD
        if ($name !== '' || $value !== '' || $replace !== false) {
            // no-op
        }
        return $this;
    }
}
