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
 * @category  Zend
 * @package   Zend_Navigation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Navigation_Container
 */
#require_once 'Zend/Navigation/Container.php';

/**
 * Base class for Zend_Navigation_Page pages
 *
 * @category  Zend
 * @package   Zend_Navigation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Navigation_Page extends Zend_Navigation_Container
{
    /**
     * Page label
     *
     * @var string|null
     */
    protected $_label;

    /**
     * Fragment identifier (anchor identifier)
     *
     * The fragment identifier (anchor identifier) pointing to an anchor within
     * a resource that is subordinate to another, primary resource.
     * The fragment identifier introduced by a hash mark "#".
     * Example: http://www.example.org/foo.html#bar ("bar" is the fragment identifier)
     *
     * @link http://www.w3.org/TR/html401/intro/intro.html#fragment-uri
     *
     * @var string|null
     */
    protected $_fragment;

    /**
     * Page id
     *
     * @var string|null
     */
    protected $_id;

    /**
     * Style class for this page (CSS)
     *
     * @var string|null
     */
    protected $_class;

    /**
     * A more descriptive title for this page
     *
     * @var string|null
     */
    protected $_title;

    /**
     * This page's target
     *
     * @var string|null
     */
    protected $_target;

    /**
     * Accessibility key character
     *
     * This attribute assigns an access key to an element. An access key is a
     * single character from the document character set.
     *
     * @link http://www.w3.org/TR/html401/interact/forms.html#access-keys
     *
     * @var string|null
     */
    protected $_accesskey;

    /**
     * Forward links to other pages
     *
     * @link http://www.w3.org/TR/html4/struct/links.html#h-12.3.1
     *
     * @var array
     */
    protected $_rel = array();

    /**
     * Reverse links to other pages
     *
     * @link http://www.w3.org/TR/html4/struct/links.html#h-12.3.1
     *
     * @var array
     */
    protected $_rev = array();

    /**
     * Page order used by parent container
     *
     * @var int|null
     */
    protected $_order;

    /**
     * ACL resource associated with this page
     *
     * @var string|Zend_Acl_Resource_Interface|null
     */
    protected $_resource;

    /**
     * ACL privilege associated with this page
     *
     * @var string|null
     */
    protected $_privilege;

    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = false;

    /**
     * Whether this page should be considered visible
     *
     * @var bool
     */
    protected $_visible = true;

    /**
     * Parent container
     *
     * @var Zend_Navigation_Container|null
     */
    protected $_parent;

    /**
     * Custom page properties, used by __set(), __get() and __isset()
     *
     * @var array
     */
    protected $_properties = array();

    /**
     * Custom HTML attributes
     *
     * @var array
     */
    protected $_customHtmlAttribs = array();

    /**
     * The type of page to use when it wasn't set
     *
     * @var string
     */
    protected static $_defaultPageType;

    // Initialization:

    /**
     * Factory for Zend_Navigation_Page classes
     *
     * A specific type to construct can be specified by specifying the key
     * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
     * to Zend_Navigation_Page_Uri or Zend_Navigation_Page_Mvc. Any other value
     * for 'type' will be considered the full name of the class to construct.
     * A valid custom page class must extend Zend_Navigation_Page.
     *
     * If 'type' is not given, the type of page to construct will be determined
     * by the following rules:
     * - If $options contains either of the keys 'action', 'controller',
     *   'module', or 'route', a Zend_Navigation_Page_Mvc page will be created.
     * - If $options contains the key 'uri', a Zend_Navigation_Page_Uri page
     *   will be created.
     *
     * @param  array|Zend_Config $options  options used for creating page
     * @return Zend_Navigation_Page        a page instance
     * @throws Zend_Navigation_Exception   if $options is not array/Zend_Config
     * @throws Zend_Exception              if 'type' is specified and
     *                                     Zend_Loader is unable to load the
     *                                     class
     * @throws Zend_Navigation_Exception   if something goes wrong during
     *                                     instantiation of the page
     * @throws Zend_Navigation_Exception   if 'type' is given, and the specified
     *                                     type does not extend this class
     * @throws Zend_Navigation_Exception   if unable to determine which class
     *                                     to instantiate
     */
    public static function factory($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $options must be an array or Zend_Config');
        }

        if (isset($options['type'])) {
            $type = $options['type'];
        } elseif(self::getDefaultPageType()!= null) {
            $type = self::getDefaultPageType();
        }

        if(isset($type)) {
            if (is_string($type) && !empty($type)) {
                switch (strtolower($type)) {
                    case 'mvc':
                        $type = 'Zend_Navigation_Page_Mvc';
                        break;
                    case 'uri':
                        $type = 'Zend_Navigation_Page_Uri';
                        break;
                }

                if (!class_exists($type)) {
                    #require_once 'Zend/Loader.php';
                    @Zend_Loader::loadClass($type);
                }

                $page = new $type($options);
                if (!$page instanceof Zend_Navigation_Page) {
                    #require_once 'Zend/Navigation/Exception.php';
                    throw new Zend_Navigation_Exception(sprintf(
                            'Invalid argument: Detected type "%s", which ' .
                            'is not an instance of Zend_Navigation_Page',
                            $type));
                }
                return $page;
            }
        }

        $hasUri = isset($options['uri']);
        $hasMvc = isset($options['action']) || isset($options['controller']) ||
                  isset($options['module']) || isset($options['route']) ||
                  isset($options['params']);

        if ($hasMvc) {
            #require_once 'Zend/Navigation/Page/Mvc.php';
            return new Zend_Navigation_Page_Mvc($options);
        } elseif ($hasUri) {
            #require_once 'Zend/Navigation/Page/Uri.php';
            return new Zend_Navigation_Page_Uri($options);
        } else {
            #require_once 'Zend/Navigation/Exception.php';

            $message = 'Invalid argument: Unable to determine class to instantiate';
            if (isset($options['label'])) {
                $message .= ' (Page label: ' . $options['label'] . ')';
        }

            throw new Zend_Navigation_Exception($message);
    }
    }

    /**
     * Page constructor
     *
     * @param  array|Zend_Config $options   [optional] page options. Default is
     *                                      null, which should set defaults.
     * @throws Zend_Navigation_Exception    if invalid options are given
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        // do custom initialization
        $this->_init();
    }

    /**
     * Initializes page (used by subclasses)
     *
     * @return void
     */
    protected function _init()
    {
    }

    /**
     * Sets page properties using a Zend_Config object
     *
     * @param  Zend_Config $config        config object to get properties from
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid options are given
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Sets page properties using options from an associative array
     *
     * Each key in the array corresponds to the according set*() method, and
     * each word is separated by underscores, e.g. the option 'target'
     * corresponds to setTarget(), and the option 'reset_params' corresponds to
     * the method setResetParams().
     *
     * @param  array $options             associative array of options to set
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid options are given
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets page label
     *
     * @param  string $label              new page label
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if empty/no string is given
     */
    public function setLabel($label)
    {
        if (null !== $label && !is_string($label)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $label must be a string or null');
        }

        $this->_label = $label;
        return $this;
    }

    /**
     * Returns page label
     *
     * @return string  page label or null
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Sets a fragment identifier
     *
     * @param  string $fragment   new fragment identifier
     * @return Zend_Navigation_Page         fluent interface, returns self
     * @throws Zend_Navigation_Exception    if empty/no string is given
     */
    public function setFragment($fragment)
    {
        if (null !== $fragment && !is_string($fragment)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $fragment must be a string or null');
        }

        $this->_fragment = $fragment;
        return $this;
    }

     /**
     * Returns fragment identifier
     *
     * @return string|null  fragment identifier
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * Sets page id
     *
     * @param  string|null $id            [optional] id to set. Default is null,
     *                                    which sets no id.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if not given string or null
     */
    public function setId($id = null)
    {
        if (null !== $id && !is_string($id) && !is_numeric($id)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $id must be a string, number or null');
        }

        $this->_id = null === $id ? $id : (string) $id;

        return $this;
    }

    /**
     * Returns page id
     *
     * @return string|null  page id or null
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets page CSS class
     *
     * @param  string|null $class         [optional] CSS class to set. Default
     *                                    is null, which sets no CSS class.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if not given string or null
     */
    public function setClass($class = null)
    {
        if (null !== $class && !is_string($class)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $class must be a string or null');
        }

        $this->_class = $class;
        return $this;
    }

    /**
     * Returns page class (CSS)
     *
     * @return string|null  page's CSS class or null
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Sets page title
     *
     * @param  string $title              [optional] page title. Default is
     *                                    null, which sets no title.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if not given string or null
     */
    public function setTitle($title = null)
    {
        if (null !== $title && !is_string($title)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $title must be a non-empty string');
        }

        $this->_title = $title;
        return $this;
    }

    /**
     * Returns page title
     *
     * @return string|null  page title or null
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets page target
     *
     * @param  string|null $target        [optional] target to set. Default is
     *                                    null, which sets no target.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if target is not string or null
     */
    public function setTarget($target = null)
    {
        if (null !== $target && !is_string($target)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $target must be a string or null');
        }

        $this->_target = $target;
        return $this;
    }

    /**
     * Returns page target
     *
     * @return string|null  page target or null
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Sets access key for this page
     *
     * @param  string|null $character     [optional] access key to set. Default
     *                                    is null, which sets no access key.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if access key is not string or null or
     *                                    if the string length not equal to one
     */
    public function setAccesskey($character = null)
    {
        if (null !== $character
            && (!is_string($character) || 1 != strlen($character)))
        {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $character must be a single character or null'
            );
        }

        $this->_accesskey = $character;
        return $this;
    }

     /**
     * Returns page access key
     *
     * @return string|null  page access key or null
     */
    public function getAccesskey()
    {
        return $this->_accesskey;
    }

    /**
     * Sets the page's forward links to other pages
     *
     * This method expects an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  array|Zend_Config $relations  [optional] an associative array of
     *                                       forward links to other pages
     * @return Zend_Navigation_Page          fluent interface, returns self
     */
    public function setRel($relations = null)
    {
        $this->_rel = array();

        if (null !== $relations) {
            if ($relations instanceof Zend_Config) {
                $relations = $relations->toArray();
            }

            if (!is_array($relations)) {
                #require_once 'Zend/Navigation/Exception.php';
                throw new Zend_Navigation_Exception(
                        'Invalid argument: $relations must be an ' .
                        'array or an instance of Zend_Config');
            }

            foreach ($relations as $name => $relation) {
                if (is_string($name)) {
                    $this->_rel[$name] = $relation;
                }
            }
        }

        return $this;
    }

    /**
     * Returns the page's forward links to other pages
     *
     * This method returns an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  string $relation  [optional] name of relation to return. If not
     *                           given, all relations will be returned.
     * @return array             an array of relations. If $relation is not
     *                           specified, all relations will be returned in
     *                           an associative array.
     */
    public function getRel($relation = null)
    {
        if (null !== $relation) {
            return isset($this->_rel[$relation]) ?
                   $this->_rel[$relation] :
                   null;
        }

        return $this->_rel;
    }

    /**
     * Sets the page's reverse links to other pages
     *
     * This method expects an associative array of reverse links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  array|Zend_Config $relations  [optional] an associative array of
     *                                       reverse links to other pages
     * @return Zend_Navigation_Page          fluent interface, returns self
     */
    public function setRev($relations = null)
    {
        $this->_rev = array();

        if (null !== $relations) {
            if ($relations instanceof Zend_Config) {
                $relations = $relations->toArray();
            }

            if (!is_array($relations)) {
                #require_once 'Zend/Navigation/Exception.php';
                throw new Zend_Navigation_Exception(
                        'Invalid argument: $relations must be an ' .
                        'array or an instance of Zend_Config');
            }

            foreach ($relations as $name => $relation) {
                if (is_string($name)) {
                    $this->_rev[$name] = $relation;
                }
            }
        }

        return $this;
    }

    /**
     * Returns the page's reverse links to other pages
     *
     * This method returns an associative array of forward links to other pages,
     * where each element's key is the name of the relation (e.g. alternate,
     * prev, next, help, etc), and the value is a mixed value that could somehow
     * be considered a page.
     *
     * @param  string $relation  [optional] name of relation to return. If not
     *                           given, all relations will be returned.
     * @return array             an array of relations. If $relation is not
     *                           specified, all relations will be returned in
     *                           an associative array.
     */
    public function getRev($relation = null)
    {
        if (null !== $relation) {
            return isset($this->_rev[$relation]) ?
                   $this->_rev[$relation] :
                   null;
        }

        return $this->_rev;
    }

    /**
     * Sets a single custom HTML attribute
     *
     * @param  string      $name            name of the HTML attribute
     * @param  string|null $value           value for the HTML attribute
     * @return Zend_Navigation_Page         fluent interface, returns self
     * @throws Zend_Navigation_Exception    if name is not string or value is
     *                                      not null or a string
     */
    public function setCustomHtmlAttrib($name, $value)
    {
        if (!is_string($name)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $name must be a string'
            );
        }

        if (null !== $value && !is_string($value)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $value must be a string or null'
            );
        }

        if (null === $value && isset($this->_customHtmlAttribs[$name])) {
            unset($this->_customHtmlAttribs[$name]);
        } else {
            $this->_customHtmlAttribs[$name] = $value;
        }

        return $this;
    }

    /**
     * Returns a single custom HTML attributes by name
     *
     * @param  string $name                 name of the HTML attribute
     * @return string|null                  value for the HTML attribute or null
     * @throws Zend_Navigation_Exception    if name is not string
     */
    public function getCustomHtmlAttrib($name)
    {
        if (!is_string($name)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $name must be a string'
            );
        }

        if (isset($this->_customHtmlAttribs[$name])) {
            return $this->_customHtmlAttribs[$name];
        }

        return null;
    }

    /**
     * Sets multiple custom HTML attributes at once
     *
     * @param array $attribs        an associative array of html attributes
     * @return Zend_Navigation_Page fluent interface, returns self
     */
    public function setCustomHtmlAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setCustomHtmlAttrib($key, $value);
        }
        return $this;
    }

    /**
     * Returns all custom HTML attributes as an array
     *
     * @return array    an array containing custom HTML attributes
     */
    public function getCustomHtmlAttribs()
    {
        return $this->_customHtmlAttribs;
    }

    /**
     * Removes a custom HTML attribute from the page
     *
     * @param  string $name          name of the custom HTML attribute
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function removeCustomHtmlAttrib($name)
    {
        if (!is_string($name)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $name must be a string'
            );
        }

        if (isset($this->_customHtmlAttribs[$name])) {
            unset($this->_customHtmlAttribs[$name]);
        }
    }

    /**
     * Clear all custom HTML attributes
     *
     * @return Zend_Navigation_Page fluent interface, returns self
     */
    public function clearCustomHtmlAttribs()
    {
        $this->_customHtmlAttribs = array();

        return $this;
    }

    /**
     * Sets page order to use in parent container
     *
     * @param  int $order                 [optional] page order in container.
     *                                    Default is null, which sets no
     *                                    specific order.
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if order is not integer or null
     */
    public function setOrder($order = null)
    {
        if (is_string($order)) {
            $temp = (int) $order;
            if ($temp < 0 || $temp > 0 || $order == '0') {
                $order = $temp;
            }
        }

        if (null !== $order && !is_int($order)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $order must be an integer or null, ' .
                    'or a string that casts to an integer');
        }

        $this->_order = $order;

        // notify parent, if any
        if (isset($this->_parent)) {
            $this->_parent->notifyOrderUpdated();
        }

        return $this;
    }

    /**
     * Returns page order used in parent container
     *
     * @return int|null  page order or null
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Sets ACL resource assoicated with this page
     *
     * @param  string|Zend_Acl_Resource_Interface $resource  [optional] resource
     *                                                       to associate with
     *                                                       page. Default is
     *                                                       null, which sets no
     *                                                       resource.
     * @throws Zend_Navigation_Exception                     if $resource if
     *                                                       invalid
     * @return Zend_Navigation_Page                          fluent interface,
     *                                                       returns self
     */
    public function setResource($resource = null)
    {
        if (null === $resource || is_string($resource) ||
            $resource instanceof Zend_Acl_Resource_Interface) {
            $this->_resource = $resource;
        } else {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $resource must be null, a string, ' .
                    ' or an instance of Zend_Acl_Resource_Interface');
        }

        return $this;
    }

    /**
     * Returns ACL resource assoicated with this page
     *
     * @return string|Zend_Acl_Resource_Interface|null  ACL resource or null
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Sets ACL privilege associated with this page
     *
     * @param  string|null $privilege  [optional] ACL privilege to associate
     *                                 with this page. Default is null, which
     *                                 sets no privilege.
     * @return Zend_Navigation_Page    fluent interface, returns self
     */
    public function setPrivilege($privilege = null)
    {
        $this->_privilege = is_string($privilege) ? $privilege : null;
        return $this;
    }

    /**
     * Returns ACL privilege associated with this page
     *
     * @return string|null  ACL privilege or null
     */
    public function getPrivilege()
    {
        return $this->_privilege;
    }

    /**
     * Sets whether page should be considered active or not
     *
     * @param  bool $active          [optional] whether page should be
     *                               considered active or not. Default is true.
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function setActive($active = true)
    {
        $this->_active = (bool) $active;
        return $this;
    }

    /**
     * Returns whether page should be considered active or not
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active && $recursive) {
            foreach ($this->_pages as $page) {
                if ($page->isActive(true)) {
                    return true;
                }
            }
            return false;
        }

        return $this->_active;
    }

    /**
     * Proxy to isActive()
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default
     *                          is false.
     * @return bool             whether page should be considered active
     */
    public function getActive($recursive = false)
    {
        return $this->isActive($recursive);
    }

    /**
     * Sets whether the page should be visible or not
     *
     * @param  bool $visible         [optional] whether page should be
     *                               considered visible or not. Default is true.
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function setVisible($visible = true)
    {
        if (is_string($visible) && 'false' == strtolower($visible)) {
            $visible = false;
        }
        $this->_visible = (bool) $visible;
        return $this;
    }

    /**
     * Returns a boolean value indicating whether the page is visible
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          invisible if parent is invisible. Default is
     *                          false.
     * @return bool             whether page should be considered visible
     */
    public function isVisible($recursive = false)
    {
        if ($recursive && isset($this->_parent) &&
            $this->_parent instanceof Zend_Navigation_Page) {
            if (!$this->_parent->isVisible(true)) {
                return false;
            }
        }

        return $this->_visible;
    }

    /**
     * Proxy to isVisible()
     *
     * Returns a boolean value indicating whether the page is visible
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          invisible if parent is invisible. Default is
     *                          false.
     * @return bool             whether page should be considered visible
     */
    public function getVisible($recursive = false)
    {
        return $this->isVisible($recursive);
    }

    /**
     * Sets parent container
     *
     * @param  Zend_Navigation_Container $parent  [optional] new parent to set.
     *                                            Default is null which will set
     *                                            no parent.
     * @return Zend_Navigation_Page               fluent interface, returns self
     */
    public function setParent(Zend_Navigation_Container $parent = null)
    {
        if ($parent === $this) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'A page cannot have itself as a parent');
        }

        // return if the given parent already is parent
        if ($parent === $this->_parent) {
            return $this;
        }

        // remove from old parent
        if (null !== $this->_parent) {
            $this->_parent->removePage($this);
        }

        // set new parent
        $this->_parent = $parent;

        // add to parent if page and not already a child
        if (null !== $this->_parent && !$this->_parent->hasPage($this, false)) {
            $this->_parent->addPage($this);
        }

        return $this;
    }

    /**
     * Returns parent container
     *
     * @return Zend_Navigation_Container|null  parent container or null
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Sets the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * set method will be used. Otherwise, it will be set as a custom property.
     *
     * @param  string $property           property name
     * @param  mixed  $value              value to set
     * @return Zend_Navigation_Page       fluent interface, returns self
     * @throws Zend_Navigation_Exception  if property name is invalid
     */
    public function set($property, $value)
    {
        if (!is_string($property) || empty($property)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $property must be a non-empty string');
        }

        $method = 'set' . self::_normalizePropertyName($property);

        if ($method != 'setOptions' && $method != 'setConfig' &&
            method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->_properties[$property] = $value;
        }

        return $this;
    }

    /**
     * Returns the value of the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * get method will be used. Otherwise, it will return the matching custom
     * property, or null if not found.
     *
     * @param  string $property           property name
     * @return mixed                      the property's value or null
     * @throws Zend_Navigation_Exception  if property name is invalid
     */
    public function get($property)
    {
        if (!is_string($property) || empty($property)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $property must be a non-empty string');
        }

        $method = 'get' . self::_normalizePropertyName($property);

        if (method_exists($this, $method)) {
            return $this->$method();
        } elseif (isset($this->_properties[$property])) {
            return $this->_properties[$property];
        }

        return null;
    }

    // Magic overloads:

    /**
     * Sets a custom property
     *
     * Magic overload for enabling <code>$page->propname = $value</code>.
     *
     * @param  string $name               property name
     * @param  mixed  $value              value to set
     * @return void
     * @throws Zend_Navigation_Exception  if property name is invalid
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Returns a property, or null if it doesn't exist
     *
     * Magic overload for enabling <code>$page->propname</code>.
     *
     * @param  string $name               property name
     * @return mixed                      property value or null
     * @throws Zend_Navigation_Exception  if property name is invalid
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Checks if a property is set
     *
     * Magic overload for enabling <code>isset($page->propname)</code>.
     *
     * Returns true if the property is native (id, class, title, etc), and
     * true or false if it's a custom property (depending on whether the
     * property actually is set).
     *
     * @param  string $name  property name
     * @return bool          whether the given property exists
     */
    public function __isset($name)
    {
        $method = 'get' . self::_normalizePropertyName($name);
        if (method_exists($this, $method)) {
            return true;
        }

        return isset($this->_properties[$name]);
    }

    /**
     * Unsets the given custom property
     *
     * Magic overload for enabling <code>unset($page->propname)</code>.
     *
     * @param  string $name               property name
     * @return void
     * @throws Zend_Navigation_Exception  if the property is native
     */
    public function __unset($name)
    {
        $method = 'set' . self::_normalizePropertyName($name);
        if (method_exists($this, $method)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(sprintf(
                    'Unsetting native property "%s" is not allowed',
                    $name));
        }

        if (isset($this->_properties[$name])) {
            unset($this->_properties[$name]);
        }
    }

    /**
     * Returns page label
     *
     * Magic overload for enabling <code>echo $page</code>.
     *
     * @return string  page label
     */
    public function __toString()
    {
        return $this->_label;
    }

    // Public methods:

    /**
     * Adds a forward relation to the page
     *
     * @param  string $relation      relation name (e.g. alternate, glossary,
     *                               canonical, etc)
     * @param  mixed  $value         value to set for relation
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function addRel($relation, $value)
    {
        if (is_string($relation)) {
            $this->_rel[$relation] = $value;
        }
        return $this;
    }

    /**
     * Adds a reverse relation to the page
     *
     * @param  string $relation      relation name (e.g. alternate, glossary,
     *                               canonical, etc)
     * @param  mixed  $value         value to set for relation
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function addRev($relation, $value)
    {
        if (is_string($relation)) {
            $this->_rev[$relation] = $value;
        }
        return $this;
    }

    /**
     * Removes a forward relation from the page
     *
     * @param  string $relation      name of relation to remove
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function removeRel($relation)
    {
        if (isset($this->_rel[$relation])) {
            unset($this->_rel[$relation]);
        }

        return $this;
    }

    /**
     * Removes a reverse relation from the page
     *
     * @param  string $relation      name of relation to remove
     * @return Zend_Navigation_Page  fluent interface, returns self
     */
    public function removeRev($relation)
    {
        if (isset($this->_rev[$relation])) {
            unset($this->_rev[$relation]);
        }

        return $this;
    }

    /**
     * Returns an array containing the defined forward relations
     *
     * @return array  defined forward relations
     */
    public function getDefinedRel()
    {
        return array_keys($this->_rel);
    }

    /**
     * Returns an array containing the defined reverse relations
     *
     * @return array  defined reverse relations
     */
    public function getDefinedRev()
    {
        return array_keys($this->_rev);
    }

    /**
     * Returns custom properties as an array
     *
     * @return array  an array containing custom properties
     */
    public function getCustomProperties()
    {
        return $this->_properties;
    }

    /**
     * Returns a hash code value for the page
     *
     * @return string  a hash code value for this page
     */
    public final function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * Returns an array representation of the page
     *
     * @return array  associative array containing all page properties
     */
    public function toArray()
    {
        return array_merge(
            $this->getCustomProperties(),
            array(
                'label'             => $this->getlabel(),
                'fragment'          => $this->getFragment(),
                'id'                => $this->getId(),
                'class'             => $this->getClass(),
                'title'             => $this->getTitle(),
                'target'            => $this->getTarget(),
                'accesskey'         => $this->getAccesskey(),
                'rel'               => $this->getRel(),
                'rev'               => $this->getRev(),
                'customHtmlAttribs' => $this->getCustomHtmlAttribs(),
                'order'             => $this->getOrder(),
                'resource'          => $this->getResource(),
                'privilege'         => $this->getPrivilege(),
                'active'            => $this->isActive(),
                'visible'           => $this->isVisible(),
                'type'              => get_class($this),
                'pages'             => parent::toArray()
            )
        );
    }

    // Internal methods:

    /**
     * Normalizes a property name
     *
     * @param  string $property  property name to normalize
     * @return string            normalized property name
     */
    protected static function _normalizePropertyName($property)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }

    public static function setDefaultPageType($type = null) {
        if($type !== null && !is_string($type)) {
            throw new Zend_Navigation_Exception(
                'Cannot set default page type: type is no string but should be'
            );
        }

        self::$_defaultPageType = $type;
    }

    public static function getDefaultPageType() {
        return self::$_defaultPageType;
    }

    // Abstract methods:

    /**
     * Returns href for this page
     *
     * @return string  the page's href
     */
    abstract public function getHref();
}
