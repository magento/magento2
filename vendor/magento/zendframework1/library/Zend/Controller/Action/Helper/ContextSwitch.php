<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
#require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Simplify context switching based on requested format
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_ContextSwitch extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Trigger type constants
     */
    const TRIGGER_INIT = 'TRIGGER_INIT';
    const TRIGGER_POST = 'TRIGGER_POST';

    /**
     * Supported contexts
     * @var array
     */
    protected $_contexts = array();

    /**
     * JSON auto-serialization flag
     * @var boolean
     */
    protected $_autoJsonSerialization = true;

    /**
     * Controller property key to utilize for context switching
     * @var string
     */
    protected $_contextKey = 'contexts';

    /**
     * Request parameter containing requested context
     * @var string
     */
    protected $_contextParam = 'format';

    /**
     * Current context
     * @var string
     */
    protected $_currentContext;

    /**
     * Default context (xml)
     * @var string
     */
    protected $_defaultContext = 'xml';

    /**
     * Whether or not to disable layouts when switching contexts
     * @var boolean
     */
    protected $_disableLayout = true;

    /**
     * Methods that require special configuration
     * @var array
     */
    protected $_specialConfig = array(
        'setSuffix',
        'setHeaders',
        'setCallbacks',
    );

    /**
     * Methods that are not configurable via setOptions and setConfig
     * @var array
     */
    protected $_unconfigurable = array(
        'setOptions',
        'setConfig',
        'setHeader',
        'setCallback',
        'setContext',
        'setActionContext',
        'setActionContexts',
    );

    /**
     * @var Zend_Controller_Action_Helper_ViewRenderer
     */
    protected $_viewRenderer;

    /**
     * Original view suffix prior to detecting context switch
     * @var string
     */
    protected $_viewSuffixOrig;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        } elseif (is_array($options)) {
            $this->setOptions($options);
        }

        if (empty($this->_contexts)) {
            $this->addContexts(array(
                'json' => array(
                    'suffix'    => 'json',
                    'headers'   => array('Content-Type' => 'application/json'),
                    'callbacks' => array(
                        'init' => 'initJsonContext',
                        'post' => 'postJsonContext'
                    )
                ),
                'xml'  => array(
                    'suffix'    => 'xml',
                    'headers'   => array('Content-Type' => 'application/xml'),
                )
            ));
        }

        $this->init();
    }

    /**
     * Initialize at start of action controller
     *
     * Reset the view script suffix to the original state, or store the
     * original state.
     *
     * @return void
     */
    public function init()
    {
        if (null === $this->_viewSuffixOrig) {
            $this->_viewSuffixOrig = $this->_getViewRenderer()->getViewSuffix();
        } else {
            $this->_getViewRenderer()->setViewSuffix($this->_viewSuffixOrig);
        }
    }

    /**
     * Configure object from array of options
     *
     * @param  array $options
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setOptions(array $options)
    {
        if (isset($options['contexts'])) {
            $this->setContexts($options['contexts']);
            unset($options['contexts']);
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $this->_unconfigurable)) {
                continue;
            }

            if (in_array($method, $this->_specialConfig)) {
                $method = '_' . $method;
            }

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set object state from config object
     *
     * @param  Zend_Config $config
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Strategy pattern: return object
     *
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function direct()
    {
        return $this;
    }

    /**
     * Initialize context detection and switching
     *
     * @param  mixed $format
     * @throws Zend_Controller_Action_Exception
     * @return void
     */
    public function initContext($format = null)
    {
        $this->_currentContext = null;

        $controller = $this->getActionController();
        $request    = $this->getRequest();
        $action     = $request->getActionName();

        // Return if no context switching enabled, or no context switching
        // enabled for this action
        $contexts = $this->getActionContexts($action);
        if (empty($contexts)) {
            return;
        }

        // Return if no context parameter provided
        if (!$context = $request->getParam($this->getContextParam())) {
            if ($format === null) {
                return;
            }
            $context = $format;
            $format  = null;
        }

        // Check if context allowed by action controller
        if (!$this->hasActionContext($action, $context)) {
            return;
        }

        // Return if invalid context parameter provided and no format or invalid
        // format provided
        if (!$this->hasContext($context)) {
            if (empty($format) || !$this->hasContext($format)) {

                return;
            }
        }

        // Use provided format if passed
        if (!empty($format) && $this->hasContext($format)) {
            $context = $format;
        }

        $suffix = $this->getSuffix($context);

        $this->_getViewRenderer()->setViewSuffix($suffix);

        $headers = $this->getHeaders($context);
        if (!empty($headers)) {
            $response = $this->getResponse();
            foreach ($headers as $header => $content) {
                $response->setHeader($header, $content);
            }
        }

        if ($this->getAutoDisableLayout()) {
            /**
             * @see Zend_Layout
             */
            #require_once 'Zend/Layout.php';
            $layout = Zend_Layout::getMvcInstance();
            if (null !== $layout) {
                $layout->disableLayout();
            }
        }

        if (null !== ($callback = $this->getCallback($context, self::TRIGGER_INIT))) {
            if (is_string($callback) && method_exists($this, $callback)) {
                $this->$callback();
            } elseif (is_string($callback) && function_exists($callback)) {
                $callback();
            } elseif (is_array($callback)) {
                call_user_func($callback);
            } else {
                /**
                 * @see Zend_Controller_Action_Exception
                 */
                #require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception(sprintf('Invalid context callback registered for context "%s"', $context));
            }
        }

        $this->_currentContext = $context;
    }

    /**
     * JSON context extra initialization
     *
     * Turns off viewRenderer auto-rendering
     *
     * @return void
     */
    public function initJsonContext()
    {
        if (!$this->getAutoJsonSerialization()) {
            return;
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if ($view instanceof Zend_View_Interface) {
            $viewRenderer->setNoRender(true);
        }
    }

    /**
     * Should JSON contexts auto-serialize?
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setAutoJsonSerialization($flag)
    {
        $this->_autoJsonSerialization = (bool) $flag;
        return $this;
    }

    /**
     * Get JSON context auto-serialization flag
     *
     * @return boolean
     */
    public function getAutoJsonSerialization()
    {
        return $this->_autoJsonSerialization;
    }

    /**
     * Set suffix from array
     *
     * @param  array $spec
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    protected function _setSuffix(array $spec)
    {
        foreach ($spec as $context => $suffixInfo) {
            if (!is_string($context)) {
                $context = null;
            }

            if (is_string($suffixInfo)) {
                $this->setSuffix($context, $suffixInfo);
                continue;
            } elseif (is_array($suffixInfo)) {
                if (isset($suffixInfo['suffix'])) {
                    $suffix                    = $suffixInfo['suffix'];
                    $prependViewRendererSuffix = true;

                    if ((null === $context) && isset($suffixInfo['context'])) {
                        $context = $suffixInfo['context'];
                    }

                    if (isset($suffixInfo['prependViewRendererSuffix'])) {
                        $prependViewRendererSuffix = $suffixInfo['prependViewRendererSuffix'];
                    }

                    $this->setSuffix($context, $suffix, $prependViewRendererSuffix);
                    continue;
                }

                $count = count($suffixInfo);
                switch (true) {
                    case (($count < 2) && (null === $context)):
                        /**
                         * @see Zend_Controller_Action_Exception
                         */
                        #require_once 'Zend/Controller/Action/Exception.php';
                        throw new Zend_Controller_Action_Exception('Invalid suffix information provided in config');
                    case ($count < 2):
                        $suffix = array_shift($suffixInfo);
                        $this->setSuffix($context, $suffix);
                        break;
                    case (($count < 3) && (null === $context)):
                        $context = array_shift($suffixInfo);
                        $suffix  = array_shift($suffixInfo);
                        $this->setSuffix($context, $suffix);
                        break;
                    case (($count == 3) && (null === $context)):
                        $context = array_shift($suffixInfo);
                        $suffix  = array_shift($suffixInfo);
                        $prependViewRendererSuffix = array_shift($suffixInfo);
                        $this->setSuffix($context, $suffix, $prependViewRendererSuffix);
                        break;
                    case ($count >= 2):
                        $suffix  = array_shift($suffixInfo);
                        $prependViewRendererSuffix = array_shift($suffixInfo);
                        $this->setSuffix($context, $suffix, $prependViewRendererSuffix);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * Customize view script suffix to use when switching context.
     *
     * Passing an empty suffix value to the setters disables the view script
     * suffix change.
     *
     * @param  string  $context                   Context type for which to set suffix
     * @param  string  $suffix                    Suffix to use
     * @param  boolean $prependViewRendererSuffix Whether or not to prepend the new suffix to the viewrenderer suffix
     * @throws Zend_Controller_Action_Exception
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setSuffix($context, $suffix, $prependViewRendererSuffix = true)
    {
        if (!isset($this->_contexts[$context])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Cannot set suffix; invalid context type "%s"', $context));
        }

        if (empty($suffix)) {
            $suffix = '';
        }

        if (is_array($suffix)) {
            if (isset($suffix['prependViewRendererSuffix'])) {
                $prependViewRendererSuffix = $suffix['prependViewRendererSuffix'];
            }
            if (isset($suffix['suffix'])) {
                $suffix = $suffix['suffix'];
            } else {
                $suffix = '';
            }
        }

        $suffix = (string) $suffix;

        if ($prependViewRendererSuffix) {
            if (empty($suffix)) {
                $suffix = $this->_getViewRenderer()->getViewSuffix();
            } else {
                $suffix .= '.' . $this->_getViewRenderer()->getViewSuffix();
            }
        }

        $this->_contexts[$context]['suffix'] = $suffix;
        return $this;
    }

    /**
     * Retrieve suffix for given context type
     *
     * @param  string $type Context type
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    public function getSuffix($type)
    {
        if (!isset($this->_contexts[$type])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Cannot retrieve suffix; invalid context type "%s"', $type));
        }

        return $this->_contexts[$type]['suffix'];
    }

    /**
     * Does the given context exist?
     *
     * @param  string  $context
     * @param  boolean $throwException
     * @throws Zend_Controller_Action_Exception if context does not exist and throwException is true
     * @return bool
     */
    public function hasContext($context, $throwException = false)
    {
        if (is_string($context)) {
            if (isset($this->_contexts[$context])) {
                return true;
            }
        } elseif (is_array($context)) {
            $error = false;
            foreach ($context as $test) {
                if (!isset($this->_contexts[$test])) {
                    $error = (string) $test;
                    break;
                }
            }
            if (false === $error) {
                return true;
            }
            $context = $error;
        } elseif (true === $context) {
            return true;
        }

        if ($throwException) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Context "%s" does not exist', $context));
        }

        return false;
    }

    /**
     * Add header to context
     *
     * @param  string $context
     * @param  string $header
     * @param  string $content
     * @throws Zend_Controller_Action_Exception
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function addHeader($context, $header, $content)
    {
        $context = (string) $context;
        $this->hasContext($context, true);

        $header  = (string) $header;
        $content = (string) $content;

        if (isset($this->_contexts[$context]['headers'][$header])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Cannot add "%s" header to context "%s": already exists', $header, $context));
        }

        $this->_contexts[$context]['headers'][$header] = $content;
        return $this;
    }

    /**
     * Customize response header to use when switching context
     *
     * Passing an empty header value to the setters disables the response
     * header.
     *
     * @param  string $type   Context type for which to set suffix
     * @param  string $header Header to set
     * @param  string $content Header content
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setHeader($context, $header, $content)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        $header  = (string) $header;
        $content = (string) $content;

        $this->_contexts[$context]['headers'][$header] = $content;
        return $this;
    }

    /**
     * Add multiple headers at once for a given context
     *
     * @param  string $context
     * @param  array  $headers
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function addHeaders($context, array $headers)
    {
        foreach ($headers as $header => $content) {
            $this->addHeader($context, $header, $content);
        }

        return $this;
    }

    /**
     * Set headers from context => headers pairs
     *
     * @param  array $options
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    protected function _setHeaders(array $options)
    {
        foreach ($options as $context => $headers) {
            if (!is_array($headers)) {
                continue;
            }
            $this->setHeaders($context, $headers);
        }

        return $this;
    }

    /**
     * Set multiple headers at once for a given context
     *
     * @param  string $context
     * @param  array  $headers
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setHeaders($context, array $headers)
    {
        $this->clearHeaders($context);
        foreach ($headers as $header => $content) {
            $this->setHeader($context, $header, $content);
        }

        return $this;
    }

    /**
     * Retrieve context header
     *
     * Returns the value of a given header for a given context type
     *
     * @param  string $context
     * @param  string $header
     * @return string|null
     */
    public function getHeader($context, $header)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        $header  = (string) $header;
        if (isset($this->_contexts[$context]['headers'][$header])) {
            return $this->_contexts[$context]['headers'][$header];
        }

        return null;
    }

    /**
     * Retrieve context headers
     *
     * Returns all headers for a context as key/value pairs
     *
     * @param  string $context
     * @return array
     */
    public function getHeaders($context)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        return $this->_contexts[$context]['headers'];
    }

    /**
     * Remove a single header from a context
     *
     * @param  string $context
     * @param  string $header
     * @return boolean
     */
    public function removeHeader($context, $header)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        $header  = (string) $header;
        if (isset($this->_contexts[$context]['headers'][$header])) {
            unset($this->_contexts[$context]['headers'][$header]);
            return true;
        }

        return false;
    }

    /**
     * Clear all headers for a given context
     *
     * @param  string $context
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function clearHeaders($context)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        $this->_contexts[$context]['headers'] = array();
        return $this;
    }

    /**
     * Validate trigger and return in normalized form
     *
     * @param  string $trigger
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    protected function _validateTrigger($trigger)
    {
        $trigger = strtoupper($trigger);
        if ('TRIGGER_' !== substr($trigger, 0, 8)) {
            $trigger = 'TRIGGER_' . $trigger;
        }

        if (!in_array($trigger, array(self::TRIGGER_INIT, self::TRIGGER_POST))) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Invalid trigger "%s"', $trigger));
        }

        return $trigger;
    }

    /**
     * Set a callback for a given context and trigger
     *
     * @param  string       $context
     * @param  string       $trigger
     * @param  string|array $callback
     * @throws Zend_Controller_Action_Exception
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setCallback($context, $trigger, $callback)
    {
        $this->hasContext($context, true);
        $trigger = $this->_validateTrigger($trigger);

        if (!is_string($callback)) {
            if (!is_array($callback) || (2 != count($callback))) {
                /**
                 * @see Zend_Controller_Action_Exception
                 */
                #require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('Invalid callback specified');
            }
        }

        $this->_contexts[$context]['callbacks'][$trigger] = $callback;
        return $this;
    }

    /**
     * Set callbacks from array of context => callbacks pairs
     *
     * @param  array $options
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    protected function _setCallbacks(array $options)
    {
        foreach ($options as $context => $callbacks) {
            if (!is_array($callbacks)) {
                continue;
            }

            $this->setCallbacks($context, $callbacks);
        }
        return $this;
    }

    /**
     * Set callbacks for a given context
     *
     * Callbacks should be in trigger/callback pairs.
     *
     * @param  string $context
     * @param  array  $callbacks
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setCallbacks($context, array $callbacks)
    {
        $this->hasContext($context, true);
        $context = (string) $context;
        if (!isset($this->_contexts[$context]['callbacks'])) {
            $this->_contexts[$context]['callbacks'] = array();
        }

        foreach ($callbacks as $trigger => $callback) {
            $this->setCallback($context, $trigger, $callback);
        }
        return $this;
    }

    /**
     * Get a single callback for a given context and trigger
     *
     * @param  string $context
     * @param  string $trigger
     * @return string|array|null
     */
    public function getCallback($context, $trigger)
    {
        $this->hasContext($context, true);
        $trigger = $this->_validateTrigger($trigger);
        if (isset($this->_contexts[$context]['callbacks'][$trigger])) {
            return $this->_contexts[$context]['callbacks'][$trigger];
        }

        return null;
    }

    /**
     * Get all callbacks for a given context
     *
     * @param  string $context
     * @return array
     */
    public function getCallbacks($context)
    {
        $this->hasContext($context, true);
        return $this->_contexts[$context]['callbacks'];
    }

    /**
     * Clear a callback for a given context and trigger
     *
     * @param  string $context
     * @param  string $trigger
     * @return boolean
     */
    public function removeCallback($context, $trigger)
    {
        $this->hasContext($context, true);
        $trigger = $this->_validateTrigger($trigger);
        if (isset($this->_contexts[$context]['callbacks'][$trigger])) {
            unset($this->_contexts[$context]['callbacks'][$trigger]);
            return true;
        }

        return false;
    }

    /**
     * Clear all callbacks for a given context
     *
     * @param  string $context
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function clearCallbacks($context)
    {
        $this->hasContext($context, true);
        $this->_contexts[$context]['callbacks'] = array();
        return $this;
    }

    /**
     * Set name of parameter to use when determining context format
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setContextParam($name)
    {
        $this->_contextParam = (string) $name;
        return $this;
    }

    /**
     * Return context format request parameter name
     *
     * @return string
     */
    public function getContextParam()
    {
        return $this->_contextParam;
    }

    /**
     * Indicate default context to use when no context format provided
     *
     * @param  string $type
     * @throws Zend_Controller_Action_Exception
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setDefaultContext($type)
    {
        if (!isset($this->_contexts[$type])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Cannot set default context; invalid context type "%s"', $type));
        }

        $this->_defaultContext = $type;
        return $this;
    }

    /**
     * Return default context
     *
     * @return string
     */
    public function getDefaultContext()
    {
        return $this->_defaultContext;
    }

    /**
     * Set flag indicating if layout should be disabled
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setAutoDisableLayout($flag)
    {
        $this->_disableLayout = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve auto layout disable flag
     *
     * @return boolean
     */
    public function getAutoDisableLayout()
    {
        return $this->_disableLayout;
    }

    /**
     * Add new context
     *
     * @param  string $context Context type
     * @param  array  $spec    Context specification
     * @throws Zend_Controller_Action_Exception
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function addContext($context, array $spec)
    {
        if ($this->hasContext($context)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf('Cannot add context "%s"; already exists', $context));
        }
        $context = (string) $context;

        $this->_contexts[$context] = array();

        $this->setSuffix($context,    (isset($spec['suffix'])    ? $spec['suffix']    : ''))
             ->setHeaders($context,   (isset($spec['headers'])   ? $spec['headers']   : array()))
             ->setCallbacks($context, (isset($spec['callbacks']) ? $spec['callbacks'] : array()));
        return $this;
    }

    /**
     * Overwrite existing context
     *
     * @param  string $context Context type
     * @param  array  $spec    Context specification
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setContext($context, array $spec)
    {
        $this->removeContext($context);
        return $this->addContext($context, $spec);
    }

    /**
     * Add multiple contexts
     *
     * @param  array $contexts
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function addContexts(array $contexts)
    {
        foreach ($contexts as $context => $spec) {
            $this->addContext($context, $spec);
        }
        return $this;
    }

    /**
     * Set multiple contexts, after first removing all
     *
     * @param  array $contexts
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setContexts(array $contexts)
    {
        $this->clearContexts();
        foreach ($contexts as $context => $spec) {
            $this->addContext($context, $spec);
        }
        return $this;
    }

    /**
     * Retrieve context specification
     *
     * @param  string $context
     * @return array|null
     */
    public function getContext($context)
    {
        if ($this->hasContext($context)) {
            return $this->_contexts[(string) $context];
        }
        return null;
    }

    /**
     * Retrieve context definitions
     *
     * @return array
     */
    public function getContexts()
    {
        return $this->_contexts;
    }

    /**
     * Remove a context
     *
     * @param  string $context
     * @return boolean
     */
    public function removeContext($context)
    {
        if ($this->hasContext($context)) {
            unset($this->_contexts[(string) $context]);
            return true;
        }
        return false;
    }

    /**
     * Remove all contexts
     *
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function clearContexts()
    {
        $this->_contexts = array();
        return $this;
    }

    /**
     * Return current context, if any
     *
     * @return null|string
     */
    public function getCurrentContext()
    {
        return $this->_currentContext;
    }

    /**
     * Post dispatch processing
     *
     * Execute postDispatch callback for current context, if available
     *
     * @throws Zend_Controller_Action_Exception
     * @return void
     */
    public function postDispatch()
    {
        $context = $this->getCurrentContext();
        if (null !== $context) {
            if (null !== ($callback = $this->getCallback($context, self::TRIGGER_POST))) {
                if (is_string($callback) && method_exists($this, $callback)) {
                    $this->$callback();
                } elseif (is_string($callback) && function_exists($callback)) {
                    $callback();
                } elseif (is_array($callback)) {
                    call_user_func($callback);
                } else {
                    /**
                     * @see Zend_Controller_Action_Exception
                     */
                    #require_once 'Zend/Controller/Action/Exception.php';
                    throw new Zend_Controller_Action_Exception(sprintf('Invalid postDispatch context callback registered for context "%s"', $context));
                }
            }
        }
    }

    /**
     * JSON post processing
     *
     * JSON serialize view variables to response body
     *
     * @return void
     */
    public function postJsonContext()
    {
        if (!$this->getAutoJsonSerialization()) {
            return;
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if ($view instanceof Zend_View_Interface) {
            /**
             * @see Zend_Json
             */
            if(method_exists($view, 'getVars')) {
                #require_once 'Zend/Json.php';
                $vars = Zend_Json::encode($view->getVars());
                $this->getResponse()->setBody($vars);
            } else {
                #require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('View does not implement the getVars() method needed to encode the view into JSON');
            }
        }
    }

    /**
     * Add one or more contexts to an action
     *
     * @param  string       $action
     * @param  string|array $context
     * @return Zend_Controller_Action_Helper_ContextSwitch|void Provides a fluent interface
     */
    public function addActionContext($action, $context)
    {
        $this->hasContext($context, true);
        $controller = $this->getActionController();
        if (null === $controller) {
            return;
        }
        $action     = (string) $action;
        $contextKey = $this->_contextKey;

        if (!isset($controller->$contextKey)) {
            $controller->$contextKey = array();
        }

        if (true === $context) {
            $contexts = $this->getContexts();
            $controller->{$contextKey}[$action] = array_keys($contexts);
            return $this;
        }

        $context = (array) $context;
        if (!isset($controller->{$contextKey}[$action])) {
            $controller->{$contextKey}[$action] = $context;
        } else {
            $controller->{$contextKey}[$action] = array_merge(
                $controller->{$contextKey}[$action],
                $context
            );
        }

        return $this;
    }

    /**
     * Set a context as available for a given controller action
     *
     * @param  string       $action
     * @param  string|array $context
     * @return Zend_Controller_Action_Helper_ContextSwitch|void Provides a fluent interface
     */
    public function setActionContext($action, $context)
    {
        $this->hasContext($context, true);
        $controller = $this->getActionController();
        if (null === $controller) {
            return;
        }
        $action     = (string) $action;
        $contextKey = $this->_contextKey;

        if (!isset($controller->$contextKey)) {
            $controller->$contextKey = array();
        }

        if (true === $context) {
            $contexts = $this->getContexts();
            $controller->{$contextKey}[$action] = array_keys($contexts);
        } else {
            $controller->{$contextKey}[$action] = (array) $context;
        }

        return $this;
    }

    /**
     * Add multiple action/context pairs at once
     *
     * @param  array $contexts
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function addActionContexts(array $contexts)
    {
        foreach ($contexts as $action => $context) {
            $this->addActionContext($action, $context);
        }
        return $this;
    }

    /**
     * Overwrite and set multiple action contexts at once
     *
     * @param  array $contexts
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setActionContexts(array $contexts)
    {
        foreach ($contexts as $action => $context) {
            $this->setActionContext($action, $context);
        }
        return $this;
    }

    /**
     * Does a particular controller action have the given context(s)?
     *
     * @param  string       $action
     * @param  string|array $context
     * @throws Zend_Controller_Action_Exception
     * @return boolean
     */
    public function hasActionContext($action, $context)
    {
        $this->hasContext($context, true);
        $controller = $this->getActionController();
        if (null === $controller) {
            return false;
        }
        $action     = (string) $action;
        $contextKey = $this->_contextKey;

        if (!isset($controller->{$contextKey})) {
            return false;
        }

        $allContexts = $controller->{$contextKey};

        if (!is_array($allContexts)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception("Invalid contexts found for controller");
        }

        if (!isset($allContexts[$action])) {
            return false;
        }

        if (true === $allContexts[$action]) {
            return true;
        }

        $contexts = $allContexts[$action];

        if (!is_array($contexts)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception(sprintf("Invalid contexts found for action '%s'", $action));
        }

        if (is_string($context) && in_array($context, $contexts)) {
            return true;
        } elseif (is_array($context)) {
            $found = true;
            foreach ($context as $test) {
                if (!in_array($test, $contexts)) {
                    $found = false;
                    break;
                }
            }
            return $found;
        }

        return false;
    }

    /**
     * Get contexts for a given action or all actions in the controller
     *
     * @param  string $action
     * @return array
     */
    public function getActionContexts($action = null)
    {
        $controller = $this->getActionController();
        if (null === $controller) {
            return array();
        }
        $contextKey = $this->_contextKey;

        if (!isset($controller->$contextKey)) {
            return array();
        }

        if (null !== $action) {
            $action = (string) $action;
            if (isset($controller->{$contextKey}[$action])) {
                return $controller->{$contextKey}[$action];
            } else {
                return array();
            }
        }

        return $controller->$contextKey;
    }

    /**
     * Remove one or more contexts for a given controller action
     *
     * @param  string       $action
     * @param  string|array $context
     * @return boolean
     */
    public function removeActionContext($action, $context)
    {
        if ($this->hasActionContext($action, $context)) {
            $controller     = $this->getActionController();
            $contextKey     = $this->_contextKey;
            $action         = (string) $action;
            $contexts       = $controller->$contextKey;
            $actionContexts = $contexts[$action];
            $contexts       = (array) $context;
            foreach ($contexts as $context) {
                $index = array_search($context, $actionContexts);
                if (false !== $index) {
                    unset($controller->{$contextKey}[$action][$index]);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Clear all contexts for a given controller action or all actions
     *
     * @param  string $action
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function clearActionContexts($action = null)
    {
        $controller = $this->getActionController();
        $contextKey = $this->_contextKey;

        if (!isset($controller->$contextKey) || empty($controller->$contextKey)) {
            return $this;
        }

        if (null === $action) {
            $controller->$contextKey = array();
            return $this;
        }

        $action = (string) $action;
        if (isset($controller->{$contextKey}[$action])) {
            unset($controller->{$contextKey}[$action]);
        }

        return $this;
    }

    /**
     * Retrieve ViewRenderer
     *
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    protected function _getViewRenderer()
    {
        if (null === $this->_viewRenderer) {
            $this->_viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        }

        return $this->_viewRenderer;
    }
}

