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
 * @see Zend_View
 */
#require_once 'Zend/View.php';

/**
 * View script integration
 *
 * Zend_Controller_Action_Helper_ViewRenderer provides transparent view
 * integration for action controllers. It allows you to create a view object
 * once, and populate it throughout all actions. Several global options may be
 * set:
 *
 * - noController: if set true, render() will not look for view scripts in
 *   subdirectories named after the controller
 * - viewSuffix: what view script filename suffix to use
 *
 * The helper autoinitializes the action controller view preDispatch(). It
 * determines the path to the class file, and then determines the view base
 * directory from there. It also uses the module name as a class prefix for
 * helpers and views such that if your module name is 'Search', it will set the
 * helper class prefix to 'Search_View_Helper' and the filter class prefix to ;
 * 'Search_View_Filter'.
 *
 * Usage:
 * <code>
 * // In your bootstrap:
 * Zend_Controller_Action_HelperBroker::addHelper(new Zend_Controller_Action_Helper_ViewRenderer());
 *
 * // In your action controller methods:
 * $viewHelper = $this->_helper->getHelper('view');
 *
 * // Don't use controller subdirectories
 * $viewHelper->setNoController(true);
 *
 * // Specify a different script to render:
 * $this->_helper->viewRenderer('form');
 *
 * </code>
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * Word delimiters
     * @var array
     */
    protected $_delimiters;

    /**
     * @var Zend_Filter_Inflector
     */
    protected $_inflector;

    /**
     * Inflector target
     * @var string
     */
    protected $_inflectorTarget = '';

    /**
     * Current module directory
     * @var string
     */
    protected $_moduleDir = '';

    /**
     * Whether or not to autorender using controller name as subdirectory;
     * global setting (not reset at next invocation)
     * @var boolean
     */
    protected $_neverController = false;

    /**
     * Whether or not to autorender postDispatch; global setting (not reset at
     * next invocation)
     * @var boolean
     */
    protected $_neverRender     = false;

    /**
     * Whether or not to use a controller name as a subdirectory when rendering
     * @var boolean
     */
    protected $_noController    = false;

    /**
     * Whether or not to autorender postDispatch; per controller/action setting (reset
     * at next invocation)
     * @var boolean
     */
    protected $_noRender        = false;

    /**
     * Characters representing path delimiters in the controller
     * @var string|array
     */
    protected $_pathDelimiters;

    /**
     * Which named segment of the response to utilize
     * @var string
     */
    protected $_responseSegment = null;

    /**
     * Which action view script to render
     * @var string
     */
    protected $_scriptAction    = null;

    /**
     * View object basePath
     * @var string
     */
    protected $_viewBasePathSpec = ':moduleDir/views';

    /**
     * View script path specification string
     * @var string
     */
    protected $_viewScriptPathSpec = ':controller/:action.:suffix';

    /**
     * View script path specification string, minus controller segment
     * @var string
     */
    protected $_viewScriptPathNoControllerSpec = ':action.:suffix';

    /**
     * View script suffix
     * @var string
     */
    protected $_viewSuffix      = 'phtml';

    /**
     * Constructor
     *
     * Optionally set view object and options.
     *
     * @param  Zend_View_Interface $view
     * @param  array               $options
     * @return void
     */
    public function __construct(Zend_View_Interface $view = null, array $options = array())
    {
        if (null !== $view) {
            $this->setView($view);
        }

        if (!empty($options)) {
            $this->_setOptions($options);
        }
    }

    /**
     * Clone - also make sure the view is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        if (isset($this->view) && $this->view instanceof Zend_View_Interface) {
            $this->view = clone $this->view;

        }
    }

    /**
     * Set the view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get current module name
     *
     * @return string
     */
    public function getModule()
    {
        $request = $this->getRequest();
        $module  = $request->getModuleName();
        if (null === $module) {
            $module = $this->getFrontController()->getDispatcher()->getDefaultModule();
        }

        return $module;
    }

    /**
     * Get module directory
     *
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    public function getModuleDirectory()
    {
        $module    = $this->getModule();
        $moduleDir = $this->getFrontController()->getControllerDirectory($module);
        if ((null === $moduleDir) || is_array($moduleDir)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('ViewRenderer cannot locate module directory for module "' . $module . '"');
        }
        $this->_moduleDir = dirname($moduleDir);
        return $this->_moduleDir;
    }

    /**
     * Get inflector
     *
     * @return Zend_Filter_Inflector
     */
    public function getInflector()
    {
        if (null === $this->_inflector) {
            /**
             * @see Zend_Filter_Inflector
             */
            #require_once 'Zend/Filter/Inflector.php';
            /**
             * @see Zend_Filter_PregReplace
             */
            #require_once 'Zend/Filter/PregReplace.php';
            /**
             * @see Zend_Filter_Word_UnderscoreToSeparator
             */
            #require_once 'Zend/Filter/Word/UnderscoreToSeparator.php';
            $this->_inflector = new Zend_Filter_Inflector();
            $this->_inflector->setStaticRuleReference('moduleDir', $this->_moduleDir) // moduleDir must be specified before the less specific 'module'
                 ->addRules(array(
                     ':module'     => array('Word_CamelCaseToDash', 'StringToLower'),
                     ':controller' => array('Word_CamelCaseToDash', new Zend_Filter_Word_UnderscoreToSeparator('/'), 'StringToLower', new Zend_Filter_PregReplace('/\./', '-')),
                     ':action'     => array('Word_CamelCaseToDash', new Zend_Filter_PregReplace('#[^a-z0-9' . preg_quote('/', '#') . ']+#i', '-'), 'StringToLower'),
                 ))
                 ->setStaticRuleReference('suffix', $this->_viewSuffix)
                 ->setTargetReference($this->_inflectorTarget);
        }

        // Ensure that module directory is current
        $this->getModuleDirectory();

        return $this->_inflector;
    }

    /**
     * Set inflector
     *
     * @param  Zend_Filter_Inflector $inflector
     * @param  boolean               $reference Whether the moduleDir, target, and suffix should be set as references to ViewRenderer properties
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setInflector(Zend_Filter_Inflector $inflector, $reference = false)
    {
        $this->_inflector = $inflector;
        if ($reference) {
            $this->_inflector->setStaticRuleReference('suffix', $this->_viewSuffix)
                 ->setStaticRuleReference('moduleDir', $this->_moduleDir)
                 ->setTargetReference($this->_inflectorTarget);
        }
        return $this;
    }

    /**
     * Set inflector target
     *
     * @param  string $target
     * @return void
     */
    protected function _setInflectorTarget($target)
    {
        $this->_inflectorTarget = (string) $target;
    }

    /**
     * Set internal module directory representation
     *
     * @param  string $dir
     * @return void
     */
    protected function _setModuleDir($dir)
    {
        $this->_moduleDir = (string) $dir;
    }

    /**
     * Get internal module directory representation
     *
     * @return string
     */
    protected function _getModuleDir()
    {
        return $this->_moduleDir;
    }

    /**
     * Generate a class prefix for helper and filter classes
     *
     * @return string
     */
    protected function _generateDefaultPrefix()
    {
        $default = 'Zend_View';
        if (null === $this->_actionController) {
            return $default;
        }

        $class = get_class($this->_actionController);

        if (!strstr($class, '_')) {
            return $default;
        }

        $module = $this->getModule();
        if ('default' == $module) {
            return $default;
        }

        $prefix = substr($class, 0, strpos($class, '_')) . '_View';

        return $prefix;
    }

    /**
     * Retrieve base path based on location of current action controller
     *
     * @return string
     */
    protected function _getBasePath()
    {
        if (null === $this->_actionController) {
            return './views';
        }

        $inflector = $this->getInflector();
        $this->_setInflectorTarget($this->getViewBasePathSpec());

        $dispatcher = $this->getFrontController()->getDispatcher();
        $request = $this->getRequest();

        $parts = array(
            'module'     => (($moduleName = $request->getModuleName()) != '') ? $dispatcher->formatModuleName($moduleName) : $moduleName,
            'controller' => $request->getControllerName(),
            'action'     => $dispatcher->formatActionName($request->getActionName())
            );

        $path = $inflector->filter($parts);
        return $path;
    }

    /**
     * Set options
     *
     * @param  array $options
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    protected function _setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            switch ($key) {
                case 'neverRender':
                case 'neverController':
                case 'noController':
                case 'noRender':
                    $property = '_' . $key;
                    $this->{$property} = ($value) ? true : false;
                    break;
                case 'responseSegment':
                case 'scriptAction':
                case 'viewBasePathSpec':
                case 'viewScriptPathSpec':
                case 'viewScriptPathNoControllerSpec':
                case 'viewSuffix':
                    $property = '_' . $key;
                    $this->{$property} = (string) $value;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * Initialize the view object
     *
     * $options may contain the following keys:
     * - neverRender - flag dis/enabling postDispatch() autorender (affects all subsequent calls)
     * - noController - flag indicating whether or not to look for view scripts in subdirectories named after the controller
     * - noRender - flag indicating whether or not to autorender postDispatch()
     * - responseSegment - which named response segment to render a view script to
     * - scriptAction - what action script to render
     * - viewBasePathSpec - specification to use for determining view base path
     * - viewScriptPathSpec - specification to use for determining view script paths
     * - viewScriptPathNoControllerSpec - specification to use for determining view script paths when noController flag is set
     * - viewSuffix - what view script filename suffix to use
     *
     * @param  string $path
     * @param  string $prefix
     * @param  array  $options
     * @throws Zend_Controller_Action_Exception
     * @return void
     */
    public function initView($path = null, $prefix = null, array $options = array())
    {
        if (null === $this->view) {
            $this->setView(new Zend_View());
        }

        // Reset some flags every time
        $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
        $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
        $this->_scriptAction     = null;
        $this->_responseSegment  = null;

        // Set options first; may be used to determine other initializations
        $this->_setOptions($options);

        // Get base view path
        if (empty($path)) {
            $path = $this->_getBasePath();
            if (empty($path)) {
                /**
                 * @see Zend_Controller_Action_Exception
                 */
                #require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('ViewRenderer initialization failed: retrieved view base path is empty');
            }
        }

        if (null === $prefix) {
            $prefix = $this->_generateDefaultPrefix();
        }

        // Determine if this path has already been registered
        $currentPaths = $this->view->getScriptPaths();
        $path         = str_replace(array('/', '\\'), '/', $path);
        $pathExists   = false;
        foreach ($currentPaths as $tmpPath) {
            $tmpPath = str_replace(array('/', '\\'), '/', $tmpPath);
            if (strstr($tmpPath, $path)) {
                $pathExists = true;
                break;
            }
        }
        if (!$pathExists) {
            $this->view->addBasePath($path, $prefix);
        }

        // Register view with action controller (unless already registered)
        if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
            $this->_actionController->view       = $this->view;
            $this->_actionController->viewSuffix = $this->_viewSuffix;
        }
    }

    /**
     * init - initialize view
     *
     * @return void
     */
    public function init()
    {
        if ($this->getFrontController()->getParam('noViewRenderer')) {
            return;
        }

        $this->initView();
    }

    /**
     * Set view basePath specification
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     *
     * @param  string $path
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setViewBasePathSpec($path)
    {
        $this->_viewBasePathSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view basePath specification string
     *
     * @return string
     */
    public function getViewBasePathSpec()
    {
        return $this->_viewBasePathSpec;
    }

    /**
     * Set view script path specification
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     *
     * @param  string $path
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setViewScriptPathSpec($path)
    {
        $this->_viewScriptPathSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view script path specification string
     *
     * @return string
     */
    public function getViewScriptPathSpec()
    {
        return $this->_viewScriptPathSpec;
    }

    /**
     * Set view script path specification (no controller variant)
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     *
     * :controller will likely be ignored in this variant.
     *
     * @param  string $path
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setViewScriptPathNoControllerSpec($path)
    {
        $this->_viewScriptPathNoControllerSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view script path specification string (no controller variant)
     *
     * @return string
     */
    public function getViewScriptPathNoControllerSpec()
    {
        return $this->_viewScriptPathNoControllerSpec;
    }

    /**
     * Get a view script based on an action and/or other variables
     *
     * Uses values found in current request if no values passed in $vars.
     *
     * If {@link $_noController} is set, uses {@link $_viewScriptPathNoControllerSpec};
     * otherwise, uses {@link $_viewScriptPathSpec}.
     *
     * @param  string $action
     * @param  array  $vars
     * @return string
     */
    public function getViewScript($action = null, array $vars = array())
    {
        $request = $this->getRequest();
        if ((null === $action) && (!isset($vars['action']))) {
            $action = $this->getScriptAction();
            if (null === $action) {
                $action = $request->getActionName();
            }
            $vars['action'] = $action;
        } elseif (null !== $action) {
            $vars['action'] = $action;
        }

        $replacePattern = array('/[^a-z0-9]+$/i', '/^[^a-z0-9]+/i');
        $vars['action'] = preg_replace($replacePattern, '', $vars['action']);

        $inflector = $this->getInflector();
        if ($this->getNoController() || $this->getNeverController()) {
            $this->_setInflectorTarget($this->getViewScriptPathNoControllerSpec());
        } else {
            $this->_setInflectorTarget($this->getViewScriptPathSpec());
        }
        return $this->_translateSpec($vars);
    }

    /**
     * Set the neverRender flag (i.e., globally dis/enable autorendering)
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setNeverRender($flag = true)
    {
        $this->_neverRender = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve neverRender flag value
     *
     * @return boolean
     */
    public function getNeverRender()
    {
        return $this->_neverRender;
    }

    /**
     * Set the noRender flag (i.e., whether or not to autorender)
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setNoRender($flag = true)
    {
        $this->_noRender = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve noRender flag value
     *
     * @return boolean
     */
    public function getNoRender()
    {
        return $this->_noRender;
    }

    /**
     * Set the view script to use
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setScriptAction($name)
    {
        $this->_scriptAction = (string) $name;
        return $this;
    }

    /**
     * Retrieve view script name
     *
     * @return string
     */
    public function getScriptAction()
    {
        return $this->_scriptAction;
    }

    /**
     * Set the response segment name
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setResponseSegment($name)
    {
        if (null === $name) {
            $this->_responseSegment = null;
        } else {
            $this->_responseSegment = (string) $name;
        }

        return $this;
    }

    /**
     * Retrieve named response segment name
     *
     * @return string
     */
    public function getResponseSegment()
    {
        return $this->_responseSegment;
    }

    /**
     * Set the noController flag (i.e., whether or not to render into controller subdirectories)
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setNoController($flag = true)
    {
        $this->_noController = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve noController flag value
     *
     * @return boolean
     */
    public function getNoController()
    {
        return $this->_noController;
    }

    /**
     * Set the neverController flag (i.e., whether or not to render into controller subdirectories)
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setNeverController($flag = true)
    {
        $this->_neverController = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve neverController flag value
     *
     * @return boolean
     */
    public function getNeverController()
    {
        return $this->_neverController;
    }

    /**
     * Set view script suffix
     *
     * @param  string $suffix
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setViewSuffix($suffix)
    {
        $this->_viewSuffix = (string) $suffix;
        return $this;
    }

    /**
     * Get view script suffix
     *
     * @return string
     */
    public function getViewSuffix()
    {
        return $this->_viewSuffix;
    }

    /**
     * Set options for rendering a view script
     *
     * @param  string  $action       View script to render
     * @param  string  $name         Response named segment to render to
     * @param  boolean $noController Whether or not to render within a subdirectory named after the controller
     * @return Zend_Controller_Action_Helper_ViewRenderer Provides a fluent interface
     */
    public function setRender($action = null, $name = null, $noController = null)
    {
        if (null !== $action) {
            $this->setScriptAction($action);
        }

        if (null !== $name) {
            $this->setResponseSegment($name);
        }

        if (null !== $noController) {
            $this->setNoController($noController);
        }

        return $this;
    }

    /**
     * Inflect based on provided vars
     *
     * Allowed variables are:
     * - :moduleDir - current module directory
     * - :module - current module name
     * - :controller - current controller name
     * - :action - current action name
     * - :suffix - view script file suffix
     *
     * @param  array $vars
     * @return string
     */
    protected function _translateSpec(array $vars = array())
    {
        $inflector  = $this->getInflector();
        $request    = $this->getRequest();
        $dispatcher = $this->getFrontController()->getDispatcher();

        // Format module name
        $module = $dispatcher->formatModuleName($request->getModuleName());

        // Format controller name
        #require_once 'Zend/Filter/Word/CamelCaseToDash.php';
        $filter     = new Zend_Filter_Word_CamelCaseToDash();
        $controller = $filter->filter($request->getControllerName());
        $controller = $dispatcher->formatControllerName($controller);
        if ('Controller' == substr($controller, -10)) {
            $controller = substr($controller, 0, -10);
        }

        // Format action name
        $action = $dispatcher->formatActionName($request->getActionName());

        $params     = compact('module', 'controller', 'action');
        foreach ($vars as $key => $value) {
            switch ($key) {
                case 'module':
                case 'controller':
                case 'action':
                case 'moduleDir':
                case 'suffix':
                    $params[$key] = (string) $value;
                    break;
                default:
                    break;
            }
        }

        if (isset($params['suffix'])) {
            $origSuffix = $this->getViewSuffix();
            $this->setViewSuffix($params['suffix']);
        }
        if (isset($params['moduleDir'])) {
            $origModuleDir = $this->_getModuleDir();
            $this->_setModuleDir($params['moduleDir']);
        }

        $filtered = $inflector->filter($params);

        if (isset($params['suffix'])) {
            $this->setViewSuffix($origSuffix);
        }
        if (isset($params['moduleDir'])) {
            $this->_setModuleDir($origModuleDir);
        }

        return $filtered;
    }

    /**
     * Render a view script (optionally to a named response segment)
     *
     * Sets the noRender flag to true when called.
     *
     * @param  string $script
     * @param  string $name
     * @return void
     */
    public function renderScript($script, $name = null)
    {
        if (null === $name) {
            $name = $this->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            $this->view->render($script),
            $name
        );

        $this->setNoRender();
    }

    /**
     * Render a view based on path specifications
     *
     * Renders a view based on the view script path specifications.
     *
     * @param  string  $action
     * @param  string  $name
     * @param  boolean $noController
     * @return void
     */
    public function render($action = null, $name = null, $noController = null)
    {
        $this->setRender($action, $name, $noController);
        $path = $this->getViewScript();
        $this->renderScript($path, $name);
    }

    /**
     * Render a script based on specification variables
     *
     * Pass an action, and one or more specification variables (view script suffix)
     * to determine the view script path, and render that script.
     *
     * @param  string $action
     * @param  array  $vars
     * @param  string $name
     * @return void
     */
    public function renderBySpec($action = null, array $vars = array(), $name = null)
    {
        if (null !== $name) {
            $this->setResponseSegment($name);
        }

        $path = $this->getViewScript($action, $vars);

        $this->renderScript($path);
    }

    /**
     * postDispatch - auto render a view
     *
     * Only autorenders if:
     * - _noRender is false
     * - action controller is present
     * - request has not been re-dispatched (i.e., _forward() has not been called)
     * - response is not a redirect
     *
     * @return void
     */
    public function postDispatch()
    {
        if ($this->_shouldRender()) {
            $this->render();
        }
    }

    /**
     * Should the ViewRenderer render a view script?
     *
     * @return boolean
     */
    protected function _shouldRender()
    {
        return (!$this->getFrontController()->getParam('noViewRenderer')
            && !$this->_neverRender
            && !$this->_noRender
            && (null !== $this->_actionController)
            && $this->getRequest()->isDispatched()
            && !$this->getResponse()->isRedirect()
        );
    }

    /**
     * Use this helper as a method; proxies to setRender()
     *
     * @param  string  $action
     * @param  string  $name
     * @param  boolean $noController
     * @return void
     */
    public function direct($action = null, $name = null, $noController = null)
    {
        $this->setRender($action, $name, $noController);
    }
}
