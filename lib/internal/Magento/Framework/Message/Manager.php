<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

use Magento\Framework\Debug;
use Magento\Framework\Event;
use Psr\Log\LoggerInterface;

/**
 * Message manager model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manager implements ManagerInterface
{
    /**
     * Default message group
     */
    const DEFAULT_GROUP = 'default';

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
     * @var Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
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
     * @param Event\ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param string $defaultGroup
     */
    public function __construct(
        Session $session,
        Factory $messageFactory,
        CollectionFactory $messagesFactory,
        Event\ManagerInterface $eventManager,
        LoggerInterface $logger,
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
     * @inheritdoc
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
     * @inheritdoc
     *
     * @param bool $clear
     * @param string|null $group
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
     * @inheritdoc
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
     * @inheritdoc
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return $this
     */
    public function addMessages(array $messages, $group = null)
    {
        foreach ($messages as $message) {
            if ($message instanceof MessageInterface) {
                $this->addMessage($message, $group);
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     *
     * @param MessageInterface[] $messages
     * @param string|null $group
     * @return $this
     */
    public function addUniqueMessages(array $messages, $group = null)
    {
        $items = $this->getMessages(false, $group)->getItems();

        foreach ($messages as $message) {
            if ($message instanceof MessageInterface and !in_array($message, $items, false)) {
                $this->addMessage($message, $group);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
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
            Debug::trace(
                $exception->getTrace(),
                true,
                true,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            )
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

    /**
     * @inheritdoc
     *
     * @param \Exception $exception
     * @param string $alternativeText
     * @param string $group
     * @return $this
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText, $group = null)
    {
        $message = sprintf(
            'Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            Debug::trace(
                $exception->getTrace(),
                true,
                true,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            )
        );

        $this->logger->critical($message);
        $this->addErrorMessage($alternativeText, $group);
        return $this;
    }

    /**
     * Adds new error message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addErrorMessage($message, $group = null)
    {
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_ERROR)
                ->setText($message),
            $group
        );
        return $this;
    }

    /**
     * Adds new warning message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addWarningMessage($message, $group = null)
    {
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_WARNING)
                ->setText($message),
            $group
        );
        return $this;
    }

    /**
     * Adds new notice message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addNoticeMessage($message, $group = null)
    {
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_NOTICE)
                ->setText($message),
            $group
        );
        return $this;
    }

    /**
     * Adds new success message
     *
     * @param string $message
     * @param string|null $group
     * @return ManagerInterface
     */
    public function addSuccessMessage($message, $group = null)
    {
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_SUCCESS)
                ->setText($message),
            $group
        );
        return $this;
    }

    /**
     * Adds new complex error message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexErrorMessage($identifier, array $data = [], $group = null)
    {
        $this->assertNotEmptyIdentifier($identifier);
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_ERROR, $identifier)
                ->setData($data),
            $group
        );

        return $this;
    }

    /**
     * Adds new complex warning message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexWarningMessage($identifier, array $data = [], $group = null)
    {
        $this->assertNotEmptyIdentifier($identifier);
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_WARNING, $identifier)
                ->setData($data),
            $group
        );

        return $this;
    }

    /**
     * Adds new complex notice message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexNoticeMessage($identifier, array $data = [], $group = null)
    {
        $this->assertNotEmptyIdentifier($identifier);
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_NOTICE, $identifier)
                ->setData($data),
            $group
        );

        return $this;
    }

    /**
     * Adds new complex success message
     *
     * @param string $identifier
     * @param array $data
     * @param string|null $group
     * @return ManagerInterface
     * @throws \InvalidArgumentException
     */
    public function addComplexSuccessMessage($identifier, array $data = [], $group = null)
    {
        $this->assertNotEmptyIdentifier($identifier);
        $this->addMessage(
            $this->createMessage(MessageInterface::TYPE_SUCCESS, $identifier)
                ->setData($data),
            $group
        );

        return $this;
    }

    /**
     * Creates identified message
     *
     * @param string $type
     * @param string|null $identifier
     * @return MessageInterface
     * @throws \InvalidArgumentException
     */
    public function createMessage($type, $identifier = null)
    {
        return $this->messageFactory->create($type)
            ->setIdentifier(
                empty($identifier)
                ? MessageInterface::DEFAULT_IDENTIFIER
                : $identifier
            );
    }

    /**
     * Asserts that identifier is not empty
     *
     * @param mixed $identifier
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertNotEmptyIdentifier($identifier)
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException('Message identifier should not be empty');
        }
    }
}
