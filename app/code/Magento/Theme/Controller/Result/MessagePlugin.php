<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Result;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\MessageInterface;

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
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\View\Element\Message\InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Framework\Json\Helper\Data $jsonHelper
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
            $messages[] = [
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
