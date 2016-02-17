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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_View_Helper_Navigation_HelperAbstract
 */
#require_once 'Zend/View/Helper/Navigation/HelperAbstract.php';

/**
 * Proxy helper for retrieving navigational helpers and forwarding calls
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @method Zend_View_Helper_Navigation_Breadcrumbs breadcrumbs
 * @method Zend_View_Helper_Navigation_Links links
 * @method Zend_View_Helper_Navigation_Menu menu
 * @method Zend_View_Helper_Navigation_Sitemap sitemap
 */
class Zend_View_Helper_Navigation
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**
     * View helper namespace
     *
     * @var string
     */
    const NS = 'Zend_View_Helper_Navigation';

    /**
     * Default proxy to use in {@link render()}
     *
     * @var string
     */
    protected $_defaultProxy = 'menu';

    /**
     * Contains references to proxied helpers
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Whether container should be injected when proxying
     *
     * @var bool
     */
    protected $_injectContainer = true;

    /**
     * Whether ACL should be injected when proxying
     *
     * @var bool
     */
    protected $_injectAcl = true;

    /**
     * Whether translator should be injected when proxying
     *
     * @var bool
     */
    protected $_injectTranslator = true;

    /**
     * Helper entry point
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation           fluent interface, returns
     *                                               self
     */
    public function navigation(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Magic overload: Proxy to other navigation helpers or the container
     *
     * Examples of usage from a view script or layout:
     * <code>
     * // proxy to Menu helper and render container:
     * echo $this->navigation()->menu();
     *
     * // proxy to Breadcrumbs helper and set indentation:
     * $this->navigation()->breadcrumbs()->setIndent(8);
     *
     * // proxy to container and find all pages with 'blog' route:
     * $blogPages = $this->navigation()->findAllByRoute('blog');
     * </code>
     *
     * @param  string $method             helper name or method name in
     *                                    container
     * @param  array  $arguments          [optional] arguments to pass
     * @return mixed                      returns what the proxied call returns
     * @throws Zend_View_Exception        if proxying to a helper, and the
     *                                    helper is not an instance of the
     *                                    interface specified in
     *                                    {@link findHelper()}
     * @throws Zend_Navigation_Exception  if method does not exist in container
     */
    public function __call($method, array $arguments = array())
    {
        // check if call should proxy to another helper
        if ($helper = $this->findHelper($method, false)) {
            return call_user_func_array(array($helper, $method), $arguments);
        }

        // default behaviour: proxy call to container
        return parent::__call($method, $arguments);
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link Zend_View_Helper_Navigation_Helper}.
     *
     * @param string $proxy                        helper name
     * @param bool   $strict                       [optional] whether
     *                                             exceptions should be
     *                                             thrown if something goes
     *                                             wrong. Default is true.
     * @return Zend_View_Helper_Navigation_Helper  helper instance
     * @throws Zend_Loader_PluginLoader_Exception  if $strict is true and
     *                                             helper cannot be found
     * @throws Zend_View_Exception                 if $strict is true and
     *                                             helper does not implement
     *                                             the specified interface
     */
    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->_helpers[$proxy])) {
            return $this->_helpers[$proxy];
        }

        if (!$this->view->getPluginLoader('helper')->getPaths(self::NS)) {
            // Add navigation helper path at the beginning
            $paths = $this->view->getHelperPaths();
            $this->view->setHelperPath(null);

            $this->view->addHelperPath(
                    str_replace('_', '/', self::NS),
                    self::NS);

            foreach ($paths as $ns => $path) {
                $this->view->addHelperPath($path, $ns);
            }
        }

        if ($strict) {
            $helper = $this->view->getHelper($proxy);
        } else {
            try {
                $helper = $this->view->getHelper($proxy);
            } catch (Zend_Loader_PluginLoader_Exception $e) {
                return null;
            }
        }

        if (!$helper instanceof Zend_View_Helper_Navigation_Helper) {
            if ($strict) {
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Proxy helper "%s" is not an instance of ' .
                        'Zend_View_Helper_Navigation_Helper',
                        get_class($helper)));
                $e->setView($this->view);
                throw $e;
            }

            return null;
        }

        $this->_inject($helper);
        $this->_helpers[$proxy] = $helper;

        return $helper;
    }

    /**
     * Injects container, ACL, and translator to the given $helper if this
     * helper is configured to do so
     *
     * @param  Zend_View_Helper_Navigation_Helper $helper  helper instance
     * @return void
     */
    protected function _inject(Zend_View_Helper_Navigation_Helper $helper)
    {
        if ($this->getInjectContainer() && !$helper->hasContainer()) {
            $helper->setContainer($this->getContainer());
        }

        if ($this->getInjectAcl()) {
            if (!$helper->hasAcl()) {
                $helper->setAcl($this->getAcl());
            }
            if (!$helper->hasRole()) {
                $helper->setRole($this->getRole());
            }
        }

        if ($this->getInjectTranslator() && !$helper->hasTranslator()) {
            $helper->setTranslator($this->getTranslator());
        }
    }

    // Accessors:

    /**
     * Sets the default proxy to use in {@link render()}
     *
     * @param  string $proxy                default proxy
     * @return Zend_View_Helper_Navigation  fluent interface, returns self
     */
    public function setDefaultProxy($proxy)
    {
        $this->_defaultProxy = (string) $proxy;
        return $this;
    }

    /**
     * Returns the default proxy to use in {@link render()}
     *
     * @return string  the default proxy to use in {@link render()}
     */
    public function getDefaultProxy()
    {
        return $this->_defaultProxy;
    }

    /**
     * Sets whether container should be injected when proxying
     *
     * @param bool $injectContainer         [optional] whether container should
     *                                      be injected when proxying. Default
     *                                      is true.
     * @return Zend_View_Helper_Navigation  fluent interface, returns self
     */
    public function setInjectContainer($injectContainer = true)
    {
        $this->_injectContainer = (bool) $injectContainer;
        return $this;
    }

    /**
     * Returns whether container should be injected when proxying
     *
     * @return bool  whether container should be injected when proxying
     */
    public function getInjectContainer()
    {
        return $this->_injectContainer;
    }

    /**
     * Sets whether ACL should be injected when proxying
     *
     * @param  bool $injectAcl              [optional] whether ACL should be
     *                                      injected when proxying. Default is
     *                                      true.
     * @return Zend_View_Helper_Navigation  fluent interface, returns self
     */
    public function setInjectAcl($injectAcl = true)
    {
        $this->_injectAcl = (bool) $injectAcl;
        return $this;
    }

    /**
     * Returns whether ACL should be injected when proxying
     *
     * @return bool  whether ACL should be injected when proxying
     */
    public function getInjectAcl()
    {
        return $this->_injectAcl;
    }

    /**
     * Sets whether translator should be injected when proxying
     *
     * @param  bool $injectTranslator       [optional] whether translator should
     *                                      be injected when proxying. Default
     *                                      is true.
     * @return Zend_View_Helper_Navigation  fluent interface, returns self
     */
    public function setInjectTranslator($injectTranslator = true)
    {
        $this->_injectTranslator = (bool) $injectTranslator;
        return $this;
    }

    /**
     * Returns whether translator should be injected when proxying
     *
     * @return bool  whether translator should be injected when proxying
     */
    public function getInjectTranslator()
    {
        return $this->_injectTranslator;
    }

    // Zend_View_Helper_Navigation_Helper:

    /**
     * Renders helper
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     * @throws Zend_Loader_PluginLoader_Exception    if helper cannot be found
     * @throws Zend_View_Exception                   if helper doesn't implement
     *                                               the interface specified in
     *                                               {@link findHelper()}
     */
    public function render(Zend_Navigation_Container $container = null)
    {
        $helper = $this->findHelper($this->getDefaultProxy());
        return $helper->render($container);
    }
}
