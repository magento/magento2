<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Result;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Plugin for putting messages to cookies
 */
class MessagePlugin
{
    /**
     * Cookies name for messages
     */
    const MESSAGES_COOKIES_NAME = 'mage-messages';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ManagerInterface $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param Data $jsonHelper
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        Data $jsonHelper
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->jsonHelper = $jsonHelper;
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result
    ) {
        if (!($subject instanceof Json)) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(
                self::MESSAGES_COOKIES_NAME,
                $this->jsonHelper->jsonEncode($this->getMessages()),
                $publicCookieMetadata
            );
        }

        return $result;
    }

    /**
     * Return messages array and clean message manager messages
     *
     * @return array
     */
    protected function getMessages()
    {
        $messages = $this->getCookiesMessages();
        /** @var MessageInterface $message */
        foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
            $messages []= [
                'type' => $message->getType(),
                'text' => $this->interpretationStrategy->interpret($message),
            ];
        }
        return $messages;
    }

    /**
     * Return messages stored in cookies
     *
     * @return array
     */
    protected function getCookiesMessages()
    {
        try {
            $messages = $this->jsonHelper->jsonDecode(
                $this->cookieManager->getCookie(self::MESSAGES_COOKIES_NAME, $this->jsonHelper->jsonEncode([]))
            );
            if (!is_array($messages)) {
                $messages = [];
            }
        } catch (\Zend_Json_Exception $e) {
            $messages = [];
        }
        return $messages;
    }
}
