<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Renderer;

use ArrayAccess;
use Traversable;
use Zend\Filter\FilterChain;
use Zend\View\Exception;
use Zend\View\HelperPluginManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;
use Zend\View\Resolver\TemplatePathStack;
use Zend\View\Variables;

/**
 * Class for Zend\View\Strategy\PhpRendererStrategy to help enforce private constructs.
 *
 * Note: all private variables in this class are prefixed with "__". This is to
 * mark them as part of the internal implementation, and thus prevent conflict
 * with variables injected into the renderer.
 *
 * Convenience methods for build in helpers (@see __call):
 *
 * @method string|null basePath($file = null)
 * @method \Zend\View\Helper\Cycle cycle(array $data = array(), $name = \Zend\View\Helper\Cycle::DEFAULT_NAME)
 * @method \Zend\View\Helper\DeclareVars declareVars()
 * @method \Zend\View\Helper\Doctype doctype($doctype = null)
 * @method mixed escapeCss($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtml($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeHtmlAttr($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeJs($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method mixed escapeUrl($value, $recurse = \Zend\View\Helper\Escaper\AbstractHelper::RECURSE_NONE)
 * @method \Zend\View\Helper\FlashMessenger flashMessenger($namespace = null)
 * @method \Zend\View\Helper\Gravatar gravatar($email = "", $options = array(), $attribs = array())
 * @method \Zend\View\Helper\HeadLink headLink(array $attributes = null, $placement = \Zend\View\Helper\Placeholder\Container\AbstractContainer::APPEND)
 * @method \Zend\View\Helper\HeadMeta headMeta($content = null, $keyValue = null, $keyType = 'name', $modifiers = array(), $placement = \Zend\View\Helper\Placeholder\Container\AbstractContainer::APPEND)
 * @method \Zend\View\Helper\HeadScript headScript($mode = \Zend\View\Helper\HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method \Zend\View\Helper\HeadStyle headStyle($content = null, $placement = 'APPEND', $attributes = array())
 * @method \Zend\View\Helper\HeadTitle headTitle($title = null, $setType = null)
 * @method string htmlFlash($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlList(array $items, $ordered = false, $attribs = false, $escape = true)
 * @method string htmlObject($data = null, $type = null, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlPage($data, array $attribs = array(), array $params = array(), $content = null)
 * @method string htmlQuicktime($data, array $attribs = array(), array $params = array(), $content = null)
 * @method mixed|null identity()
 * @method \Zend\View\Helper\InlineScript inlineScript($mode = \Zend\View\Helper\HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
 * @method string|void json($data, array $jsonOptions = array())
 * @method \Zend\View\Helper\Layout layout($template = null)
 * @method \Zend\View\Helper\Navigation navigation($container = null)
 * @method string paginationControl(\Zend\Paginator\Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
 * @method string|\Zend\View\Helper\Partial partial($name = null, $values = null)
 * @method string partialLoop($name = null, $values = null)
 * @method \Zend\View\Helper\Placeholder\Container\AbstractContainer placeHolder($name = null)
 * @method string renderChildModel($child)
 * @method void renderToPlaceholder($script, $placeholder)
 * @method string serverUrl($requestUri = null)
 * @method string url($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
 * @method \Zend\View\Helper\ViewModel viewModel()
 * @method \Zend\View\Helper\Navigation\Breadcrumbs breadCrumbs($container = null)
 * @method \Zend\View\Helper\Navigation\Links links($container = null)
 * @method \Zend\View\Helper\Navigation\Menu menu($container = null)
 * @method \Zend\View\Helper\Navigation\Sitemap sitemap($container = null)
 */
class PhpRenderer implements Renderer, TreeRendererInterface
{
    /**
     * @var string Rendered content
     */
    private $__content = '';

    /**
     * @var bool Whether or not to render trees of view models
     */
    private $__renderTrees = false;

    /**
     * Template being rendered
     *
     * @var null|string
     */
    private $__template = null;

    /**
     * Queue of templates to render
     * @var array
     */
    private $__templates = array();

    /**
     * Template resolver
     *
     * @var Resolver
     */
    private $__templateResolver;

    /**
     * Script file name to execute
     *
     * @var string
     */
    private $__file = null;

    /**
     * Helper plugin manager
     *
     * @var HelperPluginManager
     */
    private $__helpers;

    /**
     * @var FilterChain
     */
    private $__filterChain;

    /**
     * @var ArrayAccess|array ArrayAccess or associative array representing available variables
     */
    private $__vars;

    /**
     * @var array Temporary variable stack; used when variables passed to render()
     */
    private $__varsCache = array();

    /**
     * Constructor.
     *
     *
     * @todo handle passing helper plugin manager, options
     * @todo handle passing filter chain, options
     * @todo handle passing variables object, options
     * @todo handle passing resolver object, options
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($config = array())
    {
        $this->init();
    }

    /**
     * Return the template engine object
     *
     * Returns the object instance, as it is its own template engine
     *
     * @return PhpRenderer
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Allow custom object initialization when extending PhpRenderer
     *
     * Triggered by {@link __construct() the constructor} as its final action.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set script resolver
     *
     * @param  Resolver $resolver
     * @return PhpRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setResolver(Resolver $resolver)
    {
        $this->__templateResolver = $resolver;
        return $this;
    }

    /**
     * Retrieve template name or template resolver
     *
     * @param  null|string $name
     * @return string|Resolver
     */
    public function resolver($name = null)
    {
        if (null === $this->__templateResolver) {
            $this->setResolver(new TemplatePathStack());
        }

        if (null !== $name) {
            return $this->__templateResolver->resolve($name, $this);
        }

        return $this->__templateResolver;
    }

    /**
     * Set variable storage
     *
     * Expects either an array, or an object implementing ArrayAccess.
     *
     * @param  array|ArrayAccess $variables
     * @return PhpRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setVars($variables)
    {
        if (!is_array($variables) && !$variables instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or ArrayAccess object; received "%s"',
                (is_object($variables) ? get_class($variables) : gettype($variables))
            ));
        }

        // Enforce a Variables container
        if (!$variables instanceof Variables) {
            $variablesAsArray = array();
            foreach ($variables as $key => $value) {
                $variablesAsArray[$key] = $value;
            }
            $variables = new Variables($variablesAsArray);
        }

        $this->__vars = $variables;
        return $this;
    }

    /**
     * Get a single variable, or all variables
     *
     * @param  mixed $key
     * @return mixed
     */
    public function vars($key = null)
    {
        if (null === $this->__vars) {
            $this->setVars(new Variables());
        }

        if (null === $key) {
            return $this->__vars;
        }
        return $this->__vars[$key];
    }

    /**
     * Get a single variable
     *
     * @param  mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if (null === $this->__vars) {
            $this->setVars(new Variables());
        }

        return $this->__vars[$key];
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $vars = $this->vars();
        return $vars[$name];
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $vars = $this->vars();
        $vars[$name] = $value;
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        $vars = $this->vars();
        return isset($vars[$name]);
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $vars = $this->vars();
        if (!isset($vars[$name])) {
            return;
        }
        unset($vars[$name]);
    }

    /**
     * Set helper plugin manager instance
     *
     * @param  string|HelperPluginManager $helpers
     * @return PhpRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setHelperPluginManager($helpers)
    {
        if (is_string($helpers)) {
            if (!class_exists($helpers)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid helper helpers class provided (%s)',
                    $helpers
                ));
            }
            $helpers = new $helpers();
        }
        if (!$helpers instanceof HelperPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Helper helpers must extend Zend\View\HelperPluginManager; got type "%s" instead',
                (is_object($helpers) ? get_class($helpers) : gettype($helpers))
            ));
        }
        $helpers->setRenderer($this);
        $this->__helpers = $helpers;

        return $this;
    }

    /**
     * Get helper plugin manager instance
     *
     * @return HelperPluginManager
     */
    public function getHelperPluginManager()
    {
        if (null === $this->__helpers) {
            $this->setHelperPluginManager(new HelperPluginManager());
        }
        return $this->__helpers;
    }

    /**
     * Get plugin instance
     *
     * @param  string     $name Name of plugin to return
     * @param  null|array $options Options to pass to plugin constructor (if not already instantiated)
     * @return AbstractHelper
     */
    public function plugin($name, array $options = null)
    {
        return $this->getHelperPluginManager()->get($name, $options);
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin manager to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  string $method
     * @param  array $argv
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $plugin = $this->plugin($method);

        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $argv);
        }

        return $plugin;
    }

    /**
     * Set filter chain
     *
     * @param  FilterChain $filters
     * @return PhpRenderer
     */
    public function setFilterChain(FilterChain $filters)
    {
        $this->__filterChain = $filters;
        return $this;
    }

    /**
     * Retrieve filter chain for post-filtering script content
     *
     * @return FilterChain
     */
    public function getFilterChain()
    {
        if (null === $this->__filterChain) {
            $this->setFilterChain(new FilterChain());
        }
        return $this->__filterChain;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param  string|Model $nameOrModel Either the template to use, or a
     *                                   ViewModel. The ViewModel must have the
     *                                   template as an option in order to be
     *                                   valid.
     * @param  null|array|Traversable $values Values to use when rendering. If none
     *                                provided, uses those in the composed
     *                                variables container.
     * @return string The script output.
     * @throws Exception\DomainException if a ViewModel is passed, but does not
     *                                   contain a template option.
     * @throws Exception\InvalidArgumentException if the values passed are not
     *                                            an array or ArrayAccess object
     * @throws Exception\RuntimeException if the template cannot be rendered
     */
    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof Model) {
            $model       = $nameOrModel;
            $nameOrModel = $model->getTemplate();
            if (empty($nameOrModel)) {
                throw new Exception\DomainException(sprintf(
                    '%s: received View Model argument, but template is empty',
                    __METHOD__
                ));
            }
            $options = $model->getOptions();
            foreach ($options as $setting => $value) {
                $method = 'set' . $setting;
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
                unset($method, $setting, $value);
            }
            unset($options);

            // Give view model awareness via ViewModel helper
            $helper = $this->plugin('view_model');
            $helper->setCurrent($model);

            $values = $model->getVariables();
            unset($model);
        }

        // find the script file name using the parent private method
        $this->addTemplate($nameOrModel);
        unset($nameOrModel); // remove $name from local scope

        $this->__varsCache[] = $this->vars();

        if (null !== $values) {
            $this->setVars($values);
        }
        unset($values);

        // extract all assigned vars (pre-escaped), but not 'this'.
        // assigns to a double-underscored variable, to prevent naming collisions
        $__vars = $this->vars()->getArrayCopy();
        if (array_key_exists('this', $__vars)) {
            unset($__vars['this']);
        }
        extract($__vars);
        unset($__vars); // remove $__vars from local scope

        while ($this->__template = array_pop($this->__templates)) {
            $this->__file = $this->resolver($this->__template);
            if (!$this->__file) {
                throw new Exception\RuntimeException(sprintf(
                    '%s: Unable to render template "%s"; resolver could not resolve to a file',
                    __METHOD__,
                    $this->__template
                ));
            }
            try {
                ob_start();
                $includeReturn = include $this->__file;
                $this->__content = ob_get_clean();
            } catch (\Exception $ex) {
                ob_end_clean();
                throw $ex;
            }
            if ($includeReturn === false && empty($this->__content)) {
                throw new Exception\UnexpectedValueException(sprintf(
                    '%s: Unable to render template "%s"; file include failed',
                    __METHOD__,
                    $this->__file
                ));
            }
        }

        $this->setVars(array_pop($this->__varsCache));

        return $this->getFilterChain()->filter($this->__content); // filter output
    }

    /**
     * Set flag indicating whether or not we should render trees of view models
     *
     * If set to true, the View instance will not attempt to render children
     * separately, but instead pass the root view model directly to the PhpRenderer.
     * It is then up to the developer to render the children from within the
     * view script.
     *
     * @param  bool $renderTrees
     * @return PhpRenderer
     */
    public function setCanRenderTrees($renderTrees)
    {
        $this->__renderTrees = (bool) $renderTrees;
        return $this;
    }

    /**
     * Can we render trees, or are we configured to do so?
     *
     * @return bool
     */
    public function canRenderTrees()
    {
        return $this->__renderTrees;
    }

    /**
     * Add a template to the stack
     *
     * @param  string $template
     * @return PhpRenderer
     */
    public function addTemplate($template)
    {
        $this->__templates[] = $template;
        return $this;
    }

    /**
     * Make sure View variables are cloned when the view is cloned.
     *
     * @return PhpRenderer
     */
    public function __clone()
    {
        $this->__vars = clone $this->vars();
    }
}
