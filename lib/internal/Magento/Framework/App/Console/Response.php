<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Console;

/**
 * @SuppressWarnings(PHPMD.ExitExpression)
 * @since 2.0.0
 */
class Response implements \Magento\Framework\App\ResponseInterface
{
    /**
     * Status code
     * Possible values:
     *  0 (successfully)
     *  1-255 (error)
     *  -1 (error)
     *
     * @var int
     * @since 2.0.0
     */
    protected $code = 0;

    /**
     * Success code
     */
    const SUCCESS = 0;

    /**
     * Error code
     */
    const ERROR = 255;

    /**
     * Text to output on send response
     *
     * @var string
     * @since 2.0.0
     */
    private $body;

    /**
     * Set whether to terminate process on send or not
     *
     * @var bool
     * @since 2.0.0
     */
    protected $terminateOnSend = true;

    /**
     * Send response to client
     *
     * @return int
     * @since 2.0.0
     */
    public function sendResponse()
    {
        if (!empty($this->body)) {
            echo $this->body;
        }
        if ($this->terminateOnSend) {
            exit($this->code);
        }
        return $this->code;
    }

    /**
     * Get body
     *
     * @return string
     * @since 2.0.0
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return void
     * @since 2.0.0
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Set exit code
     *
     * @param int $code
     * @return void
     * @since 2.0.0
     */
    public function setCode($code)
    {
        if ($code > 255) {
            $code = 255;
        }
        $this->code = $code;
    }

    /**
     * Set whether to terminate process on send or not
     *
     * @param bool $terminate
     * @return void
     * @since 2.0.0
     */
    public function terminateOnSend($terminate)
    {
        $this->terminateOnSend = $terminate;
    }
}
