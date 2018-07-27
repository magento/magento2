<?php
/**
 * Web API response.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

class Response extends \Magento\Framework\HTTP\PhpEnvironment\Response implements
    \Magento\Framework\App\Response\HttpInterface
{
    /**
     * Character set which must be used in response.
     */
    const RESPONSE_CHARSET = 'utf-8';

    /**#@+
     * Default message types.
     */
    const MESSAGE_TYPE_SUCCESS = 'success';

    const MESSAGE_TYPE_ERROR = 'error';

    const MESSAGE_TYPE_WARNING = 'warning';

    /**#@- */

    /**#@+
     * Success HTTP response codes.
     */
    const HTTP_OK = 200;

    /**#@-*/

    /**
     * Messages.
     *
     * @var array
     */
    protected $_messages = [];

    /**
     * Set header appropriate to specified MIME type.
     *
     * @param string $mimeType MIME type
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        return $this->setHeader('Content-Type', "{$mimeType}; charset=" . self::RESPONSE_CHARSET, true);
    }

    /**
     * Add message to response.
     *
     * @param string $message
     * @param string $code
     * @param array $params
     * @param string $type
     * @return $this
     */
    public function addMessage($message, $code, $params = [], $type = self::MESSAGE_TYPE_ERROR)
    {
        $params['message'] = $message;
        $params['code'] = $code;
        $this->_messages[$type][] = $params;
        return $this;
    }

    /**
     * Has messages.
     *
     * @return bool
     */
    public function hasMessages()
    {
        return (bool)count($this->_messages) > 0;
    }

    /**
     * Return messages.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Clear messages.
     *
     * @return $this
     */
    public function clearMessages()
    {
        $this->_messages = [];
        return $this;
    }
}
