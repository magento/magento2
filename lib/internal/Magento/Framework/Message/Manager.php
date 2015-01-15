<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Message manager model
 */
class Manager implements ManagerInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Factory
     */
    protected $messageFactory;

    /**
     * @var CollectionFactory
     */
    protected $messagesFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $defaultGroup;

    /**
     * @var bool
     */
    protected $hasMessages = false;

    /**
     * @param Session $session
     * @param Factory $messageFactory
     * @param CollectionFactory $messagesFactory
     * @param EventManagerInterface $eventManager
     * @param Logger $logger
     * @param string $defaultGroup
     */
    public function __construct(
        Session $session,
        Factory $messageFactory,
        CollectionFactory $messagesFactory,
        EventManagerInterface $eventManager,
        Logger $logger,
        $defaultGroup = self::DEFAULT_GROUP
    ) {
        $this->session = $session;
        $this->messageFactory = $messageFactory;
        $this->messagesFactory = $messagesFactory;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->defaultGroup = $defaultGroup;
    }

    /**
     * Retrieve default message group
     *
     * @return string
     */
    public function getDefaultGroup()
    {
        return $this->defaultGroup;
    }

    /**
     * Retrieve default  message group or custom if was set
     *
     * @param string|null $group
     * @return string
     */
    protected function prepareGroup($group)
    {
        return !empty($group) ? $group : $this->defaultGroup;
    }

    /**
     * Retrieve messages
     *
     * @param string|null $group
     * @param bool $clear
     * @return Collection
     */
    public function getMessages($clear = false, $group = null)
    {
        $group = $this->prepareGroup($group);
        if (!$this->session->getData($group)) {
            $this->session->setData($group, $this->messagesFactory->create());
        }

        if ($clear) {
            $messages = clone $this->session->getData($group);
            $this->session->getData($group)->clear();
            $this->eventManager->dispatch('session_abstract_clear_messages');
            return $messages;
        }
        return $this->session->getData($group);
    }

    /**
     * Adding new message to message collection
     *
     * @param MessageInterface $message
     * @param string|null $group
     * @return $this
     */
    public function addMessage(MessageInterface $message, $group = null)
    {
        $this->hasMessages = true;
        $this->getMessages(false, $group)->addMessage($message);
        $this->eventManager->dispatch('session_abstract_add_message');
        return $this;
    }

    /**
     * Adding messages array to message collection
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return $this
     */
    public function addMessages(array $messages, $group = null)
    {
        foreach ($messages as $message) {
            $this->addMessage($message, $group);
        }
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param string $message
     * @param string|null $group
     * @return $this
     */
    public function addError($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_ERROR, $message), $group);
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return $this
     */
    public function addWarning($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_WARNING, $message), $group);
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return $this
     */
    public function addNotice($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $message), $group);
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param string $message
     * @param string|null $group
     * @return $this
     */
    public function addSuccess($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_SUCCESS, $message), $group);
        return $this;
    }

    /**
     * Adds messages array to message collection, but doesn't add duplicates to it
     *
     * @param MessageInterface[]|MessageInterface $messages
     * @param string|null $group
     * @return $this
     */
    public function addUniqueMessages($messages, $group = null)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        if (empty($messages)) {
            return $this;
        }

        $messagesAlready = [];
        $items = $this->getMessages(false, $group)->getItems();
        foreach ($items as $item) {
            if ($item instanceof MessageInterface) {
                $text = $item->getText();
                $messagesAlready[$text] = true;
            }
        }

        foreach ($messages as $message) {
            if ($message instanceof MessageInterface) {
                $text = $message->getText();
            } else {
                // Some unknown object, add it anyway
                continue;
            }

            // Check for duplication
            if (isset($messagesAlready[$text])) {
                continue;
            }
            $messagesAlready[$text] = true;
            $this->addMessage($message, $group);
        }

        return $this;
    }

    /**
     * Not Magento exception handling
     *
     * @param \Exception $exception
     * @param string $alternativeText
     * @param string $group
     * @return $this
     */
    public function addException(\Exception $exception, $alternativeText, $group = null)
    {
        $message = sprintf(
            'Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            $exception->getTraceAsString()
        );

        $this->logger->critical($message);
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_ERROR, $alternativeText), $group);
        return $this;
    }

    /**
     * Returns false if there are any messages for customer, true - in other case
     *
     * @return bool
     */
    public function hasMessages()
    {
        return $this->hasMessages;
    }
}
