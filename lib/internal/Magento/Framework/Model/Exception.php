<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model;

/**
 * Magento Model Exception
 *
 * This class will be extended by other modules
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Exception extends \Magento\Framework\Exception\LocalizedException
{
    const ERROR_CODE_ENTITY_ALREADY_EXISTS = 456;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message);
    }

    /**
     * @param \Magento\Framework\Message\AbstractMessage $message
     * @return $this
     */
    public function addMessage(\Magento\Framework\Message\AbstractMessage $message)
    {
        if (!isset($this->messages[$message->getType()])) {
            $this->messages[$message->getType()] = [];
        }
        $this->messages[$message->getType()][] = $message;
        return $this;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getMessages($type = '')
    {
        if ('' == $type) {
            $arrRes = [];
            foreach ($this->messages as $messages) {
                $arrRes = array_merge($arrRes, $messages);
            }
            return $arrRes;
        }
        return isset($this->messages[$type]) ? $this->messages[$type] : [];
    }

    /**
     * Set or append a message to existing one
     *
     * @param string $message
     * @param bool $append
     * @return \Magento\Framework\Model\Exception
     */
    public function setMessage($message, $append = false)
    {
        if ($append) {
            $this->message .= $message;
        } else {
            $this->message = $message;
        }
        return $this;
    }
}
