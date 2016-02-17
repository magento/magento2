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
 * @see Zend_View_Helper_Navigation_Helper
 */
#require_once 'Zend/View/Helper/Navigation/Helper.php';

/**
 * @see Zend_View_Helper_HtmlElement
 */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Base class for navigational helpers
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Helper_Navigation_HelperAbstract
    extends Zend_View_Helper_HtmlElement
    implements Zend_View_Helper_Navigation_Helper
{
    /**
     * Container to operate on by default
     *
     * @var Zend_Navigation_Container
     */
    protected $_container;

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $_minDepth;

    /**
     * The maximum depth a page can have to be included when rendering
     *
     * @var int
     */
    protected $_maxDepth;

    /**
     * Indentation string
     *
     * @var string
     */
    protected $_indent = '';

    /**
     * Whether HTML/XML output should be formatted
     *
     * @var bool
     */
    protected $_formatOutput = true;

    /**
     * Prefix for IDs when they are normalized
     *
     * @var string|null
     */
    protected $_prefixForId = null;

    /**
     * Skip current prefix for IDs when they are normalized (flag)
     *
     * @var bool
     */
    protected $_skipPrefixForId = false;

    /**
     * Translator
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translator;

    /**
     * ACL to use when iterating pages
     *
     * @var Zend_Acl
     */
    protected $_acl;

    /**
     * Wheter invisible items should be rendered by this helper
     *
     * @var bool
     */
    protected $_renderInvisible = false;

    /**
     * ACL role to use when iterating pages
     *
     * @var string|Zend_Acl_Role_Interface
     */
    protected $_role;

    /**
     * Whether translator should be used for page labels and titles
     *
     * @var bool
     */
    protected $_useTranslator = true;

    /**
     * Whether ACL should be used for filtering out pages
     *
     * @var bool
     */
    protected $_useAcl = true;

    /**
     * Default ACL to use when iterating pages if not explicitly set in the
     * instance by calling {@link setAcl()}
     *
     * @var Zend_Acl
     */
    protected static $_defaultAcl;

    /**
     * Default ACL role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     *
     * @var string|Zend_Acl_Role_Interface
     */
    protected static $_defaultRole;

    // Accessors:

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link Zend_View_Helper_Navigation_Interface::setContainer()}.
     *
     * @param  Zend_Navigation_Container $container        [optional] container
     *                                                     to operate on.
     *                                                     Default is null,
     *                                                     meaning container
     *                                                     will be reset.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setContainer(Zend_Navigation_Container $container = null)
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Returns the navigation container helper operates on by default
     *
     * Implements {@link Zend_View_Helper_Navigation_Interface::getContainer()}.
     *
     * If a helper is not explicitly set in this helper instance by calling
     * {@link setContainer()} or by passing it through the helper entry point,
     * this method will look in {@link Zend_Registry} for a container by using
     * the key 'Zend_Navigation'.
     *
     * If no container is set, and nothing is found in Zend_Registry, a new
     * container will be instantiated and stored in the helper.
     *
     * @return Zend_Navigation_Container  navigation container
     */
    public function getContainer()
    {
        if (null === $this->_container) {
            // try to fetch from registry first
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Navigation')) {
                $nav = Zend_Registry::get('Zend_Navigation');
                if ($nav instanceof Zend_Navigation_Container) {
                    return $this->_container = $nav;
                }
            }

            // nothing found in registry, create new container
            #require_once 'Zend/Navigation.php';
            $this->_container = new Zend_Navigation();
        }

        return $this->_container;
    }

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param  int $minDepth                               [optional] minimum
     *                                                     depth. Default is
     *                                                     null, which sets
     *                                                     no minimum depth.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setMinDepth($minDepth = null)
    {
        if (null === $minDepth || is_int($minDepth)) {
            $this->_minDepth = $minDepth;
        } else {
            $this->_minDepth = (int) $minDepth;
        }
        return $this;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null  minimum depth or null
     */
    public function getMinDepth()
    {
        if (!is_int($this->_minDepth) || $this->_minDepth < 0) {
            return 0;
        }
        return $this->_minDepth;
    }

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param  int $maxDepth                               [optional] maximum
     *                                                     depth. Default is
     *                                                     null, which sets no
     *                                                     maximum depth.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setMaxDepth($maxDepth = null)
    {
        if (null === $maxDepth || is_int($maxDepth)) {
            $this->_maxDepth = $maxDepth;
        } else {
            $this->_maxDepth = (int) $maxDepth;
        }
        return $this;
    }

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @return int|null  maximum depth or null
     */
    public function getMaxDepth()
    {
        return $this->_maxDepth;
    }

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @param  string|int $indent                          indentation string or
     *                                                     number of spaces
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setIndent($indent)
    {
        $this->_indent = $this->_getWhitespace($indent);
        return $this;
    }

    /**
     * Returns indentation (format output is respected)
     *
     * @return string   indentation string or an empty string
     */
    public function getIndent()
    {
        if (false === $this->getFormatOutput()) {
            return '';
        }

        return $this->_indent;
    }

    /**
     * Returns the EOL character (format output is respected)
     *
     * @see self::EOL
     * @see getFormatOutput()
     *
     * @return string       standard EOL charater or an empty string
     */
    public function getEOL()
    {
        if (false === $this->getFormatOutput()) {
            return '';
        }

        return self::EOL;
    }

    /**
     * Sets whether HTML/XML output should be formatted
     *
     * @param  bool $formatOutput                   [optional] whether output
     *                                              should be formatted. Default
     *                                              is true.
     *
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setFormatOutput($formatOutput = true)
    {
        $this->_formatOutput = (bool)$formatOutput;

        return $this;
    }

    /**
     * Returns whether HTML/XML output should be formatted
     *
     * @return bool  whether HTML/XML output should be formatted
     */
    public function getFormatOutput()
    {
        return $this->_formatOutput;
    }

    /**
     * Sets prefix for IDs when they are normalized
     *
     * @param   string $prefix                              Prefix for IDs
     * @return  Zend_View_Helper_Navigation_HelperAbstract  fluent interface, returns self
     */
    public function setPrefixForId($prefix)
    {
        if (is_string($prefix)) {
            $this->_prefixForId = trim($prefix);
        }

        return $this;
    }

    /**
     * Returns prefix for IDs when they are normalized
     *
     * @return string   Prefix for
     */
    public function getPrefixForId()
    {
        if (null === $this->_prefixForId) {
            $prefix             = get_class($this);
            $this->_prefixForId = strtolower(
                    trim(substr($prefix, strrpos($prefix, '_')), '_')
                ) . '-';
        }

        return $this->_prefixForId;
    }

    /**
     * Skip the current prefix for IDs when they are normalized
     *
     * @param  bool $flag
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface, returns self
     */
    public function skipPrefixForId($flag = true)
    {
        $this->_skipPrefixForId = (bool) $flag;
        return $this;
    }

    /**
     * Sets translator to use in helper
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::setTranslator()}.
     *
     * @param  mixed $translator                           [optional] translator.
     *                                                     Expects an object of
     *                                                     type
     *                                                     {@link Zend_Translate_Adapter}
     *                                                     or {@link Zend_Translate},
     *                                                     or null. Default is
     *                                                     null, which sets no
     *                                                     translator.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setTranslator($translator = null)
    {
        if (null == $translator ||
            $translator instanceof Zend_Translate_Adapter) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        }

        return $this;
    }

    /**
     * Returns translator used in helper
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::getTranslator()}.
     *
     * @return Zend_Translate_Adapter|null  translator or null
     */
    public function getTranslator()
    {
        if (null === $this->_translator) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $this->setTranslator(Zend_Registry::get('Zend_Translate'));
            }
        }

        return $this->_translator;
    }

    /**
     * Sets ACL to use when iterating pages
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::setAcl()}.
     *
     * @param  Zend_Acl $acl                               [optional] ACL object.
     *                                                     Default is null.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setAcl(Zend_Acl $acl = null)
    {
        $this->_acl = $acl;
        return $this;
    }

    /**
     * Returns ACL or null if it isn't set using {@link setAcl()} or
     * {@link setDefaultAcl()}
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::getAcl()}.
     *
     * @return Zend_Acl|null  ACL object or null
     */
    public function getAcl()
    {
        if ($this->_acl === null && self::$_defaultAcl !== null) {
            return self::$_defaultAcl;
        }

        return $this->_acl;
    }

    /**
     * Sets ACL role(s) to use when iterating pages
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::setRole()}.
     *
     * @param  mixed $role                                 [optional] role to
     *                                                     set. Expects a string,
     *                                                     an instance of type
     *                                                     {@link Zend_Acl_Role_Interface},
     *                                                     or null. Default is
     *                                                     null, which will set
     *                                                     no role.
     * @throws Zend_View_Exception                         if $role is invalid
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setRole($role = null)
    {
        if (null === $role || is_string($role) ||
            $role instanceof Zend_Acl_Role_Interface) {
            $this->_role = $role;
        } else {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf(
                '$role must be a string, null, or an instance of '
                .  'Zend_Acl_Role_Interface; %s given',
                gettype($role)
            ));
            $e->setView($this->view);
            throw $e;
        }

        return $this;
    }

    /**
     * Returns ACL role to use when iterating pages, or null if it isn't set
     * using {@link setRole()} or {@link setDefaultRole()}
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::getRole()}.
     *
     * @return string|Zend_Acl_Role_Interface|null  role or null
     */
    public function getRole()
    {
        if ($this->_role === null && self::$_defaultRole !== null) {
            return self::$_defaultRole;
        }

        return $this->_role;
    }

    /**
     * Sets whether ACL should be used
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::setUseAcl()}.
     *
     * @param  bool $useAcl                                [optional] whether ACL
     *                                                     should be used.
     *                                                     Default is true.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setUseAcl($useAcl = true)
    {
        $this->_useAcl = (bool) $useAcl;
        return $this;
    }

    /**
     * Returns whether ACL should be used
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::getUseAcl()}.
     *
     * @return bool  whether ACL should be used
     */
    public function getUseAcl()
    {
        return $this->_useAcl;
    }

    /**
     * Return renderInvisible flag
     *
     * @return bool
     */
    public function getRenderInvisible()
    {
        return $this->_renderInvisible;
    }

    /**
     * Render invisible items?
     *
     * @param  bool $renderInvisible                       [optional] boolean flag
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface
     *                                                     returns self
     */
    public function setRenderInvisible($renderInvisible = true)
    {
        $this->_renderInvisible = (bool) $renderInvisible;
        return $this;
    }

    /**
     * Sets whether translator should be used
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::setUseTranslator()}.
     *
     * @param  bool $useTranslator                         [optional] whether
     *                                                     translator should be
     *                                                     used. Default is true.
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setUseTranslator($useTranslator = true)
    {
        $this->_useTranslator = (bool) $useTranslator;
        return $this;
    }

    /**
     * Returns whether translator should be used
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::getUseTranslator()}.
     *
     * @return bool  whether translator should be used
     */
    public function getUseTranslator()
    {
        return $this->_useTranslator;
    }

    // Magic overloads:

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param  string $method             method name in container
     * @param  array  $arguments          [optional] arguments to pass
     * @return mixed                      returns what the container returns
     * @throws Zend_Navigation_Exception  if method does not exist in container
     */
    public function __call($method, array $arguments = array())
    {
        return call_user_func_array(
                array($this->getContainer(), $method),
                $arguments);
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::__toString()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
            return '';
        }
    }

    // Public methods:

    /**
     * Finds the deepest active page in the given container
     *
     * @param  Zend_Navigation_Container $container  container to search
     * @param  int|null                  $minDepth   [optional] minimum depth
     *                                               required for page to be
     *                                               valid. Default is to use
     *                                               {@link getMinDepth()}. A
     *                                               null value means no minimum
     *                                               depth required.
     * @param  int|null                  $minDepth   [optional] maximum depth
     *                                               a page can have to be
     *                                               valid. Default is to use
     *                                               {@link getMaxDepth()}. A
     *                                               null value means no maximum
     *                                               depth required.
     * @return array                                 an associative array with
     *                                               the values 'depth' and
     *                                               'page', or an empty array
     *                                               if not found
     */
    public function findActive(Zend_Navigation_Container $container,
                               $minDepth = null,
                               $maxDepth = -1)
    {
        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }
        if ((!is_int($maxDepth) || $maxDepth < 0) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        $found  = null;
        $foundDepth = -1;
        $iterator = new RecursiveIteratorIterator($container,
                RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $page) {
            $currDepth = $iterator->getDepth();
            if ($currDepth < $minDepth || !$this->accept($page)) {
                // page is not accepted
                continue;
            }

            if ($page->isActive(false) && $currDepth > $foundDepth) {
                // found an active page at a deeper level than before
                $found = $page;
                $foundDepth = $currDepth;
            }
        }

        if (is_int($maxDepth) && $foundDepth > $maxDepth) {
            while ($foundDepth > $maxDepth) {
                if (--$foundDepth < $minDepth) {
                    $found = null;
                    break;
                }

                $found = $found->getParent();
                if (!$found instanceof Zend_Navigation_Page) {
                    $found = null;
                    break;
                }
            }
        }

        if ($found) {
            return array('page' => $found, 'depth' => $foundDepth);
        } else {
            return array();
        }
    }

    /**
     * Checks if the helper has a container
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::hasContainer()}.
     *
     * @return bool  whether the helper has a container or not
     */
    public function hasContainer()
    {
        return null !== $this->_container;
    }

    /**
     * Checks if the helper has an ACL instance
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::hasAcl()}.
     *
     * @return bool  whether the helper has a an ACL instance or not
     */
    public function hasAcl()
    {
        return null !== $this->_acl;
    }

    /**
     * Checks if the helper has an ACL role
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::hasRole()}.
     *
     * @return bool  whether the helper has a an ACL role or not
     */
    public function hasRole()
    {
        return null !== $this->_role;
    }

    /**
     * Checks if the helper has a translator
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::hasTranslator()}.
     *
     * @return bool  whether the helper has a translator or not
     */
    public function hasTranslator()
    {
        return null !== $this->_translator;
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param  Zend_Navigation_Page $page  page to generate HTML for
     * @return string                      HTML string for the given page
     */
    public function htmlify(Zend_Navigation_Page $page)
    {
        // get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();

        if ($this->getUseTranslator() && $t = $this->getTranslator()) {
            if (is_string($label) && !empty($label)) {
                $label = $t->translate($label);
            }
            if (is_string($title) && !empty($title)) {
                $title = $t->translate($title);
            }
        }

        // get attribs for anchor element
        $attribs = array_merge(
            array(
                'id'     => $page->getId(),
                'title'  => $title,
                'class'  => $page->getClass(),
                'href'   => $page->getHref(),
                'target' => $page->getTarget()
            ),
            $page->getCustomHtmlAttribs()
        );

        return '<a' . $this->_htmlAttribs($attribs) . '>'
             . $this->view->escape($label)
             . '</a>';
    }

    // Iterator filter methods:

    /**
     * Determines whether a page should be accepted when iterating
     *
     * Rules:
     * - If a page is not visible it is not accepted, unless RenderInvisible has
     *   been set to true.
     * - If helper has no ACL, page is accepted
     * - If helper has ACL, but no role, page is not accepted
     * - If helper has ACL and role:
     *  - Page is accepted if it has no resource or privilege
     *  - Page is accepted if ACL allows page's resource or privilege
     * - If page is accepted by the rules above and $recursive is true, the page
     *   will not be accepted if it is the descendant of a non-accepted page.
     *
     * @param  Zend_Navigation_Page $page      page to check
     * @param  bool                $recursive  [optional] if true, page will not
     *                                         be accepted if it is the
     *                                         descendant of a page that is not
     *                                         accepted. Default is true.
     * @return bool                            whether page should be accepted
     */
    public function accept(Zend_Navigation_Page $page, $recursive = true)
    {
        // accept by default
        $accept = true;

        if (!$page->isVisible(false) && !$this->getRenderInvisible()) {
            // don't accept invisible pages
            $accept = false;
        } elseif ($this->getUseAcl() && !$this->_acceptAcl($page)) {
            // acl is not amused
            $accept = false;
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();
            if ($parent instanceof Zend_Navigation_Page) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }

    /**
     * Determines whether a page should be accepted by ACL when iterating
     *
     * Rules:
     * - If helper has no ACL, page is accepted
     * - If page has a resource or privilege defined, page is accepted
     *   if the ACL allows access to it using the helper's role
     * - If page has no resource or privilege, page is accepted
     *
     * @param  Zend_Navigation_Page $page  page to check
     * @return bool                        whether page is accepted by ACL
     */
    protected function _acceptAcl(Zend_Navigation_Page $page)
    {
        if (!$acl = $this->getAcl()) {
            // no acl registered means don't use acl
            return true;
        }

        $role = $this->getRole();
        $resource = $page->getResource();
        $privilege = $page->getPrivilege();

        if ($resource || $privilege) {
            // determine using helper role and page resource/privilege
            return $acl->isAllowed($role, $resource, $privilege);
        }

        return true;
    }

    // Util methods:

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param  int|string $indent
     * @return string
     */
    protected function _getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link Zend_View_Helper_HtmlElement::_htmlAttribs()}.
     *
     * @param  array $attribs  an array where each key-value pair is converted
     *                         to an attribute name and value
     * @return string          an attribute string
     */
    protected function _htmlAttribs($attribs)
    {
        // filter out null values and empty string values
        foreach ($attribs as $key => $value) {
            if ($value === null || (is_string($value) && !strlen($value))) {
                unset($attribs[$key]);
            }
        }

        return parent::_htmlAttribs($attribs);
    }

    /**
     * Normalize an ID
     *
     * Extends {@link Zend_View_Helper_HtmlElement::_normalizeId()}.
     *
     * @param  string $value    ID
     * @return string           Normalized ID
     */
    protected function _normalizeId($value)
    {
        if (false === $this->_skipPrefixForId) {
            $prefix = $this->getPrefixForId();

            if (strlen($prefix)) {
                return $prefix . $value;
            }
        }

        return parent::_normalizeId($value);
    }

    // Static methods:

    /**
     * Sets default ACL to use if another ACL is not explicitly set
     *
     * @param  Zend_Acl $acl  [optional] ACL object. Default is null, which
     *                        sets no ACL object.
     * @return void
     */
    public static function setDefaultAcl(Zend_Acl $acl = null)
    {
        self::$_defaultAcl = $acl;
    }

    /**
     * Sets default ACL role(s) to use when iterating pages if not explicitly
     * set later with {@link setRole()}
     *
     * @param  mixed $role               [optional] role to set. Expects null,
     *                                   string, or an instance of
     *                                   {@link Zend_Acl_Role_Interface}.
     *                                   Default is null, which sets no default
     *                                   role.
     * @throws Zend_View_Exception       if role is invalid
     * @return void
     */
    public static function setDefaultRole($role = null)
    {
        if (null === $role ||
            is_string($role) ||
            $role instanceof Zend_Acl_Role_Interface) {
            self::$_defaultRole = $role;
        } else {
            #require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception(
                '$role must be null|string|Zend_Acl_Role_Interface'
            );
        }
    }
}
