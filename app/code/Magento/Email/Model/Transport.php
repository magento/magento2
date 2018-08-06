<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see Zend_Mail_Transport_Sendmail is used for transport
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
     * Object for sending eMails
     *
     * @var \Zend_Mail_Transport_Sendmail
     */
    private $transport;

    /**
     * Email message object that should be instance of \Zend_Mail
     *
     * @var MessageInterface
     */
    private $message;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Zend_Mail_Transport_Sendmail $transport
     * @param MessageInterface $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param string|array|\Zend_Config|null $parameters Config options for sendmail parameters
     *
     * @throws \InvalidArgumentException when $message is not an instance of \Zend_Mail
     */
    public function __construct(
        \Zend_Mail_Transport_Sendmail $transport,
        MessageInterface $message,
        ScopeConfigInterface $scopeConfig
    ) {
        if (!$message instanceof \Zend_Mail) {
            throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
        }
        $this->transport = $transport;
        $this->message = $message;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Sets Return-Path to email if necessary, and sends email if it is allowed by System Configurations
     *
     * @return void
     * @throws MailException
     */
    public function sendMessage()
    {
        try {
            /* configuration of whether return path should be set or no. Possible values are:
             * 0 - no
             * 1 - yes (set value as FROM address)
             * 2 - use custom value
             * @see Magento\Config\Model\Config\Source\Yesnocustom
             */
            $isSetReturnPath = $this->scopeConfig->getValue(
                self::XML_PATH_SENDING_SET_RETURN_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $returnPathValue = $this->scopeConfig->getValue(
                self::XML_PATH_SENDING_RETURN_PATH_EMAIL,
                ScopeInterface::SCOPE_STORE
            );

            if ($isSetReturnPath == '1') {
                $this->message->setReturnPath($this->message->getFrom());
            } elseif ($isSetReturnPath == '2' && $returnPathValue !== null) {
                $this->message->setReturnPath($returnPathValue);
            }
            $this->transport->send($this->message);
        } catch (\Exception $e) {
            throw new MailException(__($e->getMessage()), $e);
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
