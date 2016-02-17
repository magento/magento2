<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\Mvc\Controller\Plugin\FlashMessenger as PluginFlashMessenger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper;

/**
 * Helper to proxy the plugin flash messenger
 */
class FlashMessenger extends AbstractTranslatorHelper implements ServiceLocatorAwareInterface
{
    /**
     * Default attributes for the open format tag
     *
     * @var array
     */
    protected $classMessages = array(
        PluginFlashMessenger::NAMESPACE_INFO => 'info',
        PluginFlashMessenger::NAMESPACE_ERROR => 'error',
        PluginFlashMessenger::NAMESPACE_SUCCESS => 'success',
        PluginFlashMessenger::NAMESPACE_DEFAULT => 'default',
        PluginFlashMessenger::NAMESPACE_WARNING => 'warning',
    );

    /**
     * Templates for the open/close/separators for message tags
     *
     * @var string
     */
    protected $messageCloseString     = '</li></ul>';
    protected $messageOpenFormat      = '<ul%s><li>';
    protected $messageSeparatorString = '</li><li>';

    /**
     * Flag whether to escape messages
     *
     * @var bool
     */
    protected $autoEscape = true;

    /**
     * Html escape helper
     *
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * Flash messenger plugin
     *
     * @var PluginFlashMessenger
     */
    protected $pluginFlashMessenger;

    /**
     * Service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Returns the flash messenger plugin controller
     *
     * @param  string|null $namespace
     * @return FlashMessenger|PluginFlashMessenger
     */
    public function __invoke($namespace = null)
    {
        if (null === $namespace) {
            return $this;
        }
        $flashMessenger = $this->getPluginFlashMessenger();

        return $flashMessenger->getMessagesFromNamespace($namespace);
    }

    /**
     * Proxy the flash messenger plugin controller
     *
     * @param  string $method
     * @param  array  $argv
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $flashMessenger = $this->getPluginFlashMessenger();
        return call_user_func_array(array($flashMessenger, $method), $argv);
    }

    /**
     * Render Messages
     *
     * @param  string    $namespace
     * @param  array     $classes
     * @param  null|bool $autoEscape
     * @return string
     */
    public function render($namespace = PluginFlashMessenger::NAMESPACE_DEFAULT, array $classes = array(), $autoEscape = null)
    {
        $flashMessenger = $this->getPluginFlashMessenger();
        $messages = $flashMessenger->getMessagesFromNamespace($namespace);
        return $this->renderMessages($namespace, $messages, $classes, $autoEscape);
    }

    /**
     * Render Current Messages
     *
     * @param  string    $namespace
     * @param  array     $classes
     * @param  bool|null $autoEscape
     * @return string
     */
    public function renderCurrent($namespace = PluginFlashMessenger::NAMESPACE_DEFAULT, array $classes = array(), $autoEscape = null)
    {
        $flashMessenger = $this->getPluginFlashMessenger();
        $messages = $flashMessenger->getCurrentMessagesFromNamespace($namespace);
        return $this->renderMessages($namespace, $messages, $classes, $autoEscape);
    }

    /**
     * Render Messages
     *
     * @param string    $namespace
     * @param array     $messages
     * @param array     $classes
     * @param bool|null $autoEscape
     * @return string
     */
    protected function renderMessages(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $messages = array(),
        array $classes = array(),
        $autoEscape = null
    ) {
        // Prepare classes for opening tag
        if (empty($classes)) {
            if (isset($this->classMessages[$namespace])) {
                $classes = $this->classMessages[$namespace];
            } else {
                $classes = $this->classMessages[PluginFlashMessenger::NAMESPACE_DEFAULT];
            }
            $classes = array($classes);
        }

        if (null === $autoEscape) {
            $autoEscape = $this->getAutoEscape();
        }

        // Flatten message array
        $escapeHtml      = $this->getEscapeHtmlHelper();
        $messagesToPrint = array();
        $translator = $this->getTranslator();
        $translatorTextDomain = $this->getTranslatorTextDomain();
        array_walk_recursive(
            $messages,
            function ($item) use (& $messagesToPrint, $escapeHtml, $autoEscape, $translator, $translatorTextDomain) {
                if ($translator !== null) {
                    $item = $translator->translate(
                        $item,
                        $translatorTextDomain
                    );
                }

                if ($autoEscape) {
                    $messagesToPrint[] = $escapeHtml($item);
                    return;
                }

                $messagesToPrint[] = $item;
            }
        );

        if (empty($messagesToPrint)) {
            return '';
        }

        // Generate markup
        $markup  = sprintf($this->getMessageOpenFormat(), ' class="' . implode(' ', $classes) . '"');
        $markup .= implode(
            sprintf($this->getMessageSeparatorString(), ' class="' . implode(' ', $classes) . '"'),
            $messagesToPrint
        );
        $markup .= $this->getMessageCloseString();
        return $markup;
    }

    /**
     * Set whether or not auto escaping should be used
     *
     * @param  bool $autoEscape
     * @return self
     */
    public function setAutoEscape($autoEscape = true)
    {
        $this->autoEscape = (bool) $autoEscape;
        return $this;
    }

    /**
     * Return whether auto escaping is enabled or disabled
     *
     * return bool
     */
    public function getAutoEscape()
    {
        return $this->autoEscape;
    }

    /**
     * Set the string used to close message representation
     *
     * @param  string $messageCloseString
     * @return FlashMessenger
     */
    public function setMessageCloseString($messageCloseString)
    {
        $this->messageCloseString = (string) $messageCloseString;
        return $this;
    }

    /**
     * Get the string used to close message representation
     *
     * @return string
     */
    public function getMessageCloseString()
    {
        return $this->messageCloseString;
    }

    /**
     * Set the formatted string used to open message representation
     *
     * @param  string $messageOpenFormat
     * @return FlashMessenger
     */
    public function setMessageOpenFormat($messageOpenFormat)
    {
        $this->messageOpenFormat = (string) $messageOpenFormat;
        return $this;
    }

    /**
     * Get the formatted string used to open message representation
     *
     * @return string
     */
    public function getMessageOpenFormat()
    {
        return $this->messageOpenFormat;
    }

    /**
     * Set the string used to separate messages
     *
     * @param  string $messageSeparatorString
     * @return FlashMessenger
     */
    public function setMessageSeparatorString($messageSeparatorString)
    {
        $this->messageSeparatorString = (string) $messageSeparatorString;
        return $this;
    }

    /**
     * Get the string used to separate messages
     *
     * @return string
     */
    public function getMessageSeparatorString()
    {
        return $this->messageSeparatorString;
    }

    /**
     * Set the flash messenger plugin
     *
     * @param  PluginFlashMessenger $pluginFlashMessenger
     * @return FlashMessenger
     */
    public function setPluginFlashMessenger(PluginFlashMessenger $pluginFlashMessenger)
    {
        $this->pluginFlashMessenger = $pluginFlashMessenger;
        return $this;
    }

    /**
     * Get the flash messenger plugin
     *
     * @return PluginFlashMessenger
     */
    public function getPluginFlashMessenger()
    {
        if (null === $this->pluginFlashMessenger) {
            $this->setPluginFlashMessenger(new PluginFlashMessenger());
        }

        return $this->pluginFlashMessenger;
    }

    /**
     * Set the service locator.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AbstractHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Retrieve the escapeHtml helper
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->getView(), 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (!$this->escapeHtmlHelper instanceof EscapeHtml) {
            $this->escapeHtmlHelper = new EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }
}
