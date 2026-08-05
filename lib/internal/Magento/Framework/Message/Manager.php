<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Message;

use Magento\Framework\Event;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Debug;

/**
 * Message manager model
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manager implements ManagerInterface
{
    /**
     * Default message group
     */
    public const DEFAULT_GROUP = 'default';

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
     * @var ExceptionMessageFactoryInterface
     */
    private $exceptionMessageFactory;

    /**
     * @param Session $session
     * @param Factory $messageFactory
     * @param CollectionFactory $messagesFactory
     * @param Event\ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param string $defaultGroup
     * @param ExceptionMessageFactoryInterface|null $exceptionMessageFactory
     */
    public function __construct(
        Session $session,
        Factory $messageFactory,
        CollectionFactory $messagesFactory,
        Event\ManagerInterface $eventManager,
        LoggerInterface $logger,
        $defaultGroup = self::DEFAULT_GROUP,
        ?ExceptionMessageFactoryInterface $exceptionMessageFactory = null
    ) {
        $this->session = $session;
        $this->messageFactory = $messageFactory;
        $this->messagesFactory = $messagesFactory;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->defaultGroup = $defaultGroup;
        $this->exceptionMessageFactory = $exceptionMessageFactory ?: ObjectManager::getInstance()
            ->get(ExceptionMessageLookupFactory::class); // phpcs:ignore
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
     */
    public function addError($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_ERROR, $message), $group);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addWarning($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_WARNING, $message), $group);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addNotice($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $message), $group);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addSuccess($message, $group = null)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_SUCCESS, $message), $group);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addUniqueMessages(array $messages, $group = null)
    {
        $items = $this->getMessages(false, $group)->getItems();

        foreach ($messages as $message) {
            if ($message instanceof MessageInterface && !in_array($message, $items, false)) {
                $this->addMessage($message, $group);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addException(\Exception $exception, $alternativeText = null, $group = null)
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

        if ($alternativeText) {
            $this->addError($alternativeText, $group);
        } else {
            $this->addMessage($this->exceptionMessageFactory->createMessage($exception), $group);
        }

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
     */
    public function addExceptionMessage(\Exception $exception, $alternativeText = null, $group = null)
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

        if ($alternativeText) {
            $this->addErrorMessage($alternativeText, $group);
        } else {
            $this->addMessage($this->exceptionMessageFactory->createMessage($exception), $group);
        }

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
