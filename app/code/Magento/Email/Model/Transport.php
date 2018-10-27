<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Zend\Mail\Message as ZendMessage;
use Zend\Mail\Transport\Sendmail;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see \Zend\Mail\Transport\Sendmail is used for transport
 */
class Transport implements TransportInterface
{
    /**
     * Configuration path to source of Return-Path and whether it should be set at all
     * @see \Magento\Config\Model\Config\Source\Yesnocustom to possible values
     */
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    /**
     * Configuration path for custom Return-Path email
     */
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Whether return path should be set or no.
     *
     * Possible values are:
     * 0 - no
     * 1 - yes (set value as FROM address)
     * 2 - use custom value
     *
     * @var int
     */
    private $isSetReturnPath;

    /**
     * @var string|null
     */
    private $returnPathValue;

    /**
     * @var Sendmail
     */
    private $zendTransport;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var ZendMessage
     */
    private $zendMessage;

    /**
     * @param Message $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param ZendMessage $zendMessage
     * @param null|string|array|\Traversable $parameters Config options for sendmail parameters
     */
    public function __construct(
        Message $message,
        ScopeConfigInterface $scopeConfig,
        ZendMessage $zendMessage,
        $parameters = null
    ) {
        $this->isSetReturnPath = (int) $scopeConfig->getValue(
            self::XML_PATH_SENDING_SET_RETURN_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->returnPathValue = $scopeConfig->getValue(
            self::XML_PATH_SENDING_RETURN_PATH_EMAIL,
            ScopeInterface::SCOPE_STORE
        );

        $this->zendTransport = new Sendmail($parameters);
        $this->message = $message;
        $this->zendMessage = $zendMessage;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        try {
            $message = $this->zendMessage->fromString($this->message->getRawMessage());
            if (2 === $this->isSetReturnPath && $this->returnPathValue) {
                $message->setSender($this->returnPathValue);
            } elseif (1 === $this->isSetReturnPath && $message->getFrom()->count()) {
                $fromAddressList = $message->getFrom();
                $fromAddressList->rewind();
                $message->setSender($fromAddressList->current()->getEmail());
            }

            $this->zendTransport->send($message);
        } catch (\Exception $e) {
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
