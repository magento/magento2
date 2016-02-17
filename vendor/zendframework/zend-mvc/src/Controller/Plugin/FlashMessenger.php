<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Zend\Session\Container;
use Zend\Session\ManagerInterface as Manager;
use Zend\Stdlib\SplQueue;

/**
 * Flash Messenger - implement session-based messages
 */
class FlashMessenger extends AbstractPlugin implements IteratorAggregate, Countable
{
    /**
     * Default messages namespace
     */
    const NAMESPACE_DEFAULT = 'default';

    /**
     * Success messages namespace
     */
    const NAMESPACE_SUCCESS = 'success';

    /**
     * Warning messages namespace
     */
    const NAMESPACE_WARNING = 'warning';

    /**
     * Error messages namespace
     */
    const NAMESPACE_ERROR = 'error';

    /**
     * Info messages namespace
     */
    const NAMESPACE_INFO = 'info';

    /**
     * @var Container
     */
    protected $container;

    /**
     * Messages from previous request
     * @var array
     */
    protected $messages = array();

    /**
     * @var Manager
     */
    protected $session;

    /**
     * Whether a message has been added during this request
     *
     * @var bool
     */
    protected $messageAdded = false;

    /**
     * Instance namespace, default is 'default'
     *
     * @var string
     */
    protected $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Set the session manager
     *
     * @param  Manager        $manager
     * @return FlashMessenger
     */
    public function setSessionManager(Manager $manager)
    {
        $this->session = $manager;

        return $this;
    }

    /**
     * Retrieve the session manager
     *
     * If none composed, lazy-loads a SessionManager instance
     *
     * @return Manager
     */
    public function getSessionManager()
    {
        if (!$this->session instanceof Manager) {
            $this->setSessionManager(Container::getDefaultManager());
        }

        return $this->session;
    }

    /**
     * Get session container for flash messages
     *
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container instanceof Container) {
            return $this->container;
        }

        $manager = $this->getSessionManager();
        $this->container = new Container('FlashMessenger', $manager);

        return $this->container;
    }

    /**
     * Change the namespace messages are added to
     *
     * Useful for per action controller messaging between requests
     *
     * @param  string         $namespace
     * @return FlashMessenger Provides a fluent interface
     */
    public function setNamespace($namespace = 'default')
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get the message namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Add a message
     *
     * @param  string         $message
     * @param  null|string    $namespace
     * @param  null|int       $hops
     * @return FlashMessenger Provides a fluent interface
     */
    public function addMessage($message, $namespace = null, $hops = 1)
    {
        $container = $this->getContainer();

        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if (! $this->messageAdded) {
            $this->getMessagesFromContainer();
            $container->setExpirationHops($hops, null);
        }

        if (! isset($container->{$namespace})
            || ! $container->{$namespace} instanceof SplQueue
        ) {
            $container->{$namespace} = new SplQueue();
        }

        $container->{$namespace}->push($message);

        $this->messageAdded = true;

        return $this;
    }

    /**
     * Add a message with "info" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addInfoMessage($message)
    {
        $this->addMessage($message, self::NAMESPACE_INFO);

        return $this;
    }

    /**
     * Add a message with "success" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addSuccessMessage($message)
    {
        $this->addMessage($message, self::NAMESPACE_SUCCESS);

        return $this;
    }

    /**
     * Add a message with "warning" type
     *
     * @param string        $message
     * @return FlashMessenger
     */
    public function addWarningMessage($message)
    {
        $this->addMessage($message, self::NAMESPACE_WARNING);

        return $this;
    }

    /**
     * Add a message with "error" type
     *
     * @param  string         $message
     * @return FlashMessenger
     */
    public function addErrorMessage($message)
    {
        $this->addMessage($message, self::NAMESPACE_ERROR);

        return $this;
    }

    /**
     * Whether a specific namespace has messages
     *
     * @param  string         $namespace
     * @return bool
     */
    public function hasMessages($namespace = null)
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        $this->getMessagesFromContainer();

        return isset($this->messages[$namespace]);
    }

    /**
     * Whether "info" namespace has messages
     *
     * @return bool
     */
    public function hasInfoMessages()
    {
        return $this->hasMessages(self::NAMESPACE_INFO);
    }

    /**
     * Whether "success" namespace has messages
     *
     * @return bool
     */
    public function hasSuccessMessages()
    {
        return $this->hasMessages(self::NAMESPACE_SUCCESS);
    }

    /**
     * Whether "warning" namespace has messages
     *
     * @return bool
     */
    public function hasWarningMessages()
    {
        return $this->hasMessages(self::NAMESPACE_WARNING);
    }

    /**
     * Whether "error" namespace has messages
     *
     * @return bool
     */
    public function hasErrorMessages()
    {
        return $this->hasMessages(self::NAMESPACE_ERROR);
    }

    /**
     * Get messages from a specific namespace
     *
     * @param  string         $namespace
     * @return array
     */
    public function getMessages($namespace = null)
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if ($this->hasMessages($namespace)) {
            return $this->messages[$namespace]->toArray();
        }

        return array();
    }

    /**
     * Get messages from "info" namespace
     *
     * @return array
     */
    public function getInfoMessages()
    {
        return $this->getMessages(self::NAMESPACE_INFO);
    }

    /**
     * Get messages from "success" namespace
     *
     * @return array
     */
    public function getSuccessMessages()
    {
        return $this->getMessages(self::NAMESPACE_SUCCESS);
    }

    /**
     * Get messages from "warning" namespace
     *
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->getMessages(self::NAMESPACE_WARNING);
    }

    /**
     * Get messages from "error" namespace
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->getMessages(self::NAMESPACE_ERROR);
    }

    /**
     * Clear all messages from the previous request & current namespace
     *
     * @param  string $namespace
     * @return bool True if messages were cleared, false if none existed
     */
    public function clearMessages($namespace = null)
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if ($this->hasMessages($namespace)) {
            unset($this->messages[$namespace]);

            return true;
        }

        return false;
    }

    /**
     * Clear all messages from specific namespace
     *
     * @param  string $namespaceToClear
     * @return bool True if messages were cleared, false if none existed
     */
    public function clearMessagesFromNamespace($namespaceToClear)
    {
        return $this->clearMessages($namespaceToClear);
    }

    /**
     * Clear all messages from the container
     *
     * @return bool True if messages were cleared, false if none existed
     */
    public function clearMessagesFromContainer()
    {
        $this->getMessagesFromContainer();
        if (empty($this->messages)) {
            return false;
        }
        unset($this->messages);
        $this->messages = array();

        return true;
    }

    /**
     * Check to see if messages have been added to the current
     * namespace within this request
     *
     * @param  string $namespace
     * @return bool
     */
    public function hasCurrentMessages($namespace = null)
    {
        $container = $this->getContainer();
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        return isset($container->{$namespace});
    }

    /**
     * Check to see if messages have been added to "info"
     * namespace within this request
     *
     * @return bool
     */
    public function hasCurrentInfoMessages()
    {
        return $this->hasCurrentMessages(self::NAMESPACE_INFO);
    }

    /**
     * Check to see if messages have been added to "success"
     * namespace within this request
     *
     * @return bool
     */
    public function hasCurrentSuccessMessages()
    {
        return $this->hasCurrentMessages(self::NAMESPACE_SUCCESS);
    }

    /**
     * Check to see if messages have been added to "warning"
     * namespace within this request
     *
     * @return bool
     */
    public function hasCurrentWarningMessages()
    {
        return $this->hasCurrentMessages(self::NAMESPACE_WARNING);
    }

    /**
     * Check to see if messages have been added to "error"
     * namespace within this request
     *
     * @return bool
     */
    public function hasCurrentErrorMessages()
    {
        return $this->hasCurrentMessages(self::NAMESPACE_ERROR);
    }

    /**
     * Get messages that have been added to the current
     * namespace within this request
     *
     * @param  string $namespace
     * @return array
     */
    public function getCurrentMessages($namespace = null)
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if ($this->hasCurrentMessages($namespace)) {
            $container = $this->getContainer();

            return $container->{$namespace}->toArray();
        }

        return array();
    }

    /**
     * Get messages that have been added to the "info"
     * namespace within this request
     *
     * @return array
     */
    public function getCurrentInfoMessages()
    {
        return $this->getCurrentMessages(self::NAMESPACE_INFO);
    }

    /**
     * Get messages that have been added to the "success"
     * namespace within this request
     *
     * @return array
     */
    public function getCurrentSuccessMessages()
    {
        return $this->getCurrentMessages(self::NAMESPACE_SUCCESS);
    }

    /**
     * Get messages that have been added to the "warning"
     * namespace within this request
     *
     * @return array
     */
    public function getCurrentWarningMessages()
    {
        return $this->getCurrentMessages(self::NAMESPACE_WARNING);
    }

    /**
     * Get messages that have been added to the "error"
     * namespace within this request
     *
     * @return array
     */
    public function getCurrentErrorMessages()
    {
        return $this->getCurrentMessages(self::NAMESPACE_ERROR);
    }

    /**
     * Get messages that have been added to the current
     * namespace in specific namespace
     *
     * @param  string $namespaceToGet
     * @return array
     */
    public function getCurrentMessagesFromNamespace($namespaceToGet)
    {
        return $this->getCurrentMessages($namespaceToGet);
    }

    /**
     * Clear messages from the current request and current namespace
     *
     * @param  string $namespace
     * @return bool True if current messages were cleared, false if none existed.
     */
    public function clearCurrentMessages($namespace = null)
    {
        if (null === $namespace) {
            $namespace = $this->getNamespace();
        }

        if ($this->hasCurrentMessages($namespace)) {
            $container = $this->getContainer();
            unset($container->{$namespace});

            return true;
        }

        return false;
    }

    /**
     * Clear messages from the current namespace
     *
     * @param  string $namespaceToClear
     * @return bool True if current messages were cleared from the given namespace, false if none existed.
     */
    public function clearCurrentMessagesFromNamespace($namespaceToClear)
    {
        return $this->clearCurrentMessages($namespaceToClear);
    }

    /**
     * Clear messages from the container
     *
     * @return bool True if current messages were cleared from the container, false if none existed.
     */
    public function clearCurrentMessagesFromContainer()
    {
        $container = $this->getContainer();

        $namespaces = array();
        foreach ($container as $namespace => $messages) {
            $namespaces[] = $namespace;
        }

        if (empty($namespaces)) {
            return false;
        }

        foreach ($namespaces as $namespace) {
            unset($container->{$namespace});
        }

        return true;
    }

    /**
     * Complete the IteratorAggregate interface, for iterating
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if ($this->hasMessages()) {
            return new ArrayIterator($this->getMessages());
        }

        return new ArrayIterator();
    }

    /**
     * Complete the countable interface
     *
     * @return int
     */
    public function count()
    {
        if ($this->hasMessages()) {
            return count($this->getMessages());
        }

        return 0;
    }

    /**
     * Get messages from a specific namespace
     *
     * @param  string $namespaceToGet
     * @return array
     */
    public function getMessagesFromNamespace($namespaceToGet)
    {
        return $this->getMessages($namespaceToGet);
    }

    /**
     * Pull messages from the session container
     *
     * Iterates through the session container, removing messages into the local
     * scope.
     *
     * @return void
     */
    protected function getMessagesFromContainer()
    {
        if (!empty($this->messages) || $this->messageAdded) {
            return;
        }

        $container = $this->getContainer();

        $namespaces = array();
        foreach ($container as $namespace => $messages) {
            $this->messages[$namespace] = $messages;
            $namespaces[] = $namespace;
        }

        foreach ($namespaces as $namespace) {
            unset($container->{$namespace});
        }
    }
}
