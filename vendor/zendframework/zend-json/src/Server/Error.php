<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json\Server;

class Error
{
    const ERROR_PARSE           = -32700;
    const ERROR_INVALID_REQUEST = -32600;
    const ERROR_INVALID_METHOD  = -32601;
    const ERROR_INVALID_PARAMS  = -32602;
    const ERROR_INTERNAL        = -32603;
    const ERROR_OTHER           = -32000;

    /**
     * Current code
     * @var int
     */
    protected $code = self::ERROR_OTHER;

    /**
     * Error data
     * @var mixed
     */
    protected $data;

    /**
     * Error message
     * @var string
     */
    protected $message;

    /**
     * Constructor
     *
     * @param  string $message
     * @param  int $code
     * @param  mixed $data
     */
    public function __construct($message = null, $code = self::ERROR_OTHER, $data = null)
    {
        $this->setMessage($message)
             ->setCode($code)
             ->setData($data);
    }

    /**
     * Set error code.
     *
     * If the error code is 0, it will be set to -32000 (ERROR_OTHER).
     *
     * @param  int $code
     * @return \Zend\Json\Server\Error
     */
    public function setCode($code)
    {
        if (!is_scalar($code) || is_bool($code) || is_float($code)) {
            return $this;
        }

        if (is_string($code) && !is_numeric($code)) {
            return $this;
        }

        $code = (int) $code;

        if (0 === $code) {
            $this->code = self::ERROR_OTHER;
        } else {
            $this->code = $code;
        }

        return $this;
    }

    /**
     * Get error code
     *
     * @return int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set error message
     *
     * @param  string $message
     * @return \Zend\Json\Server\Error
     */
    public function setMessage($message)
    {
        if (!is_scalar($message)) {
            return $this;
        }

        $this->message = (string) $message;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set error data
     *
     * @param  mixed $data
     * @return \Zend\Json\Server\Error
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get error data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Cast error to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'data'    => $this->getData(),
        );
    }

    /**
     * Cast error to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return \Zend\Json\Json::encode($this->toArray());
    }

    /**
     * Cast to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
