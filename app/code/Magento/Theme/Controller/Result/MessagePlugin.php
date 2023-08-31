<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Result;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Plugin for putting messages to cookies
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class MessagePlugin
{
    /**
     * Cookies name for messages
     */
    public const MESSAGES_COOKIES_NAME = 'mage-messages';

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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var InlineInterface
     */
    private $inlineTranslate;

    /**
     * @var ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param InlineInterface $inlineTranslate
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        InlineInterface $inlineTranslate,
        ConfigInterface $sessionConfig
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->serializer = $serializer;
        $this->interpretationStrategy = $interpretationStrategy;
        $this->inlineTranslate = $inlineTranslate;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Set 'mage-messages' cookie
     *
     * Checks the result that controller actions must return. If result is not JSON type, then
     * sets 'mage-messages' cookie.
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result
    ) {
        if (!($subject instanceof Json)) {
            $newMessages = [];
            foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
                $newMessages[] = [
                    'type' => $message->getType(),
                    'text' => $this->interpretationStrategy->interpret($message),
                ];
            }
            if (!empty($newMessages)) {
                $this->setMessages($this->getCookiesMessages(), $newMessages);
            }
        }
        return $result;
    }

    /**
     * Add new messages to already existing ones.
     *
     * In case if there are too many messages clear old messages.
     *
     * @param array $oldMessages
     * @param array $newMessages
     * @throws CookieSizeLimitReachedException
     */
    private function setMessages(array $oldMessages, array $newMessages): void
    {
        $messages = array_merge($oldMessages, $newMessages);
        try {
            $this->setCookie($messages);
        } catch (CookieSizeLimitReachedException $e) {
            if (empty($oldMessages)) {
                throw $e;
            }

            array_shift($oldMessages);
            $this->setMessages($oldMessages, $newMessages);
        }
    }

    /**
     * Set 'mage-messages' cookie with 'messages' array
     *
     * Checks the $messages argument. If $messages is not an empty array, then
     * sets 'mage-messages' public cookie:
     *
     *   Cookie Name: 'mage-messages';
     *   Cookie Duration: 1 year;
     *   Cookie Path: /;
     *   Cookie HTTP Only flag: FALSE. Cookie can be accessed by client-side APIs.
     *
     * The 'messages' list has format:
     * [
     *   [
     *     'type' => 'type_value',
     *     'text' => 'cookie_value',
     *   ],
     * ]
     *
     * @param array $messages List of Magento messages that must be set as 'mage-messages' cookie.
     * @return void
     */
    private function setCookie(array $messages)
    {
        if (!empty($messages)) {
            if ($this->inlineTranslate->isAllowed()) {
                foreach ($messages as &$message) {
                    $message['text'] = $this->convertMessageText($message['text']);
                }
            }

            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath($this->sessionConfig->getCookiePath());
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSameSite('Strict');

            $this->cookieManager->setPublicCookie(
                self::MESSAGES_COOKIES_NAME,
                $this->serializer->serialize($messages),
                $publicCookieMetadata
            );
        }
    }

    /**
     * Replace wrapping translation with html body.
     *
     * @param string $text
     * @return string
     */
    private function convertMessageText(string $text): string
    {
        if (preg_match('#' . ParserInterface::REGEXP_TOKEN . '#', $text, $matches)) {
            $text = $matches[1];
        }

        return $text;
    }

    /**
     * Return messages stored in cookies
     *
     * @return array
     */
    protected function getCookiesMessages()
    {
        $messages = $this->cookieManager->getCookie(self::MESSAGES_COOKIES_NAME);
        if (!$messages) {
            return [];
        }
        $messages = $this->serializer->unserialize($messages);
        if (!is_array($messages)) {
            $messages = [];
        }
        return $messages;
    }
}
