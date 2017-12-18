<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Set CookieTheme Message Observer model
 */
class SetCookieThemeMessage implements ObserverInterface
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
     * @var SerializerJson
     */
    private $serializer;

    /**
     * @param CookieManagerInterface          $cookieManager
     * @param CookieMetadataFactory           $cookieMetadataFactory
     * @param ManagerInterface                $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param SerializerJson|null             $serializer
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        SerializerJson $serializer = null
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerJson::class);
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * Set 'mage-messages' cookie
     *
     * Checks the result that controller actions must return. If result is not JSON type, then
     * sets 'mage-messages' cookie.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute(Observer $observer)
    {
        if ($this->isJsonResponse($observer) !== false) {
            $this->setCookie($this->getMessages());
        };
    }

    /**
     * Check If Response is Json
     * @param Observer $observer
     * @return bool
     */
    protected function isJsonResponse(Observer $observer)
    {
        try {
            $this->serializer->unserialize($observer->getResponse()->getBody());
        } catch (\Exception $e) {
            return false;
        }
        return true;
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
     * @param array $messages
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function setCookie(array $messages)
    {
        if (!empty($messages)) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setHttpOnly(false);

            $this->cookieManager->setPublicCookie(
                self::MESSAGES_COOKIES_NAME,
                $this->serializer->serialize($messages),
                $publicCookieMetadata
            );
        }
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
