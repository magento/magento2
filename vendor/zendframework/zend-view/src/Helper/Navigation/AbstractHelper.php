<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use RecursiveIteratorIterator;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\Navigation;
use Zend\Navigation\Page\AbstractPage;
use Zend\Permissions\Acl;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View;
use Zend\View\Exception;

/**
 * Base class for navigational helpers
 */
abstract class AbstractHelper extends View\Helper\AbstractHtmlElement implements
    EventManagerAwareInterface,
    HelperInterface,
    ServiceLocatorAwareInterface,
    TranslatorAwareInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * AbstractContainer to operate on by default
     *
     * @var Navigation\AbstractContainer
     */
    protected $container;

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $minDepth;

    /**
     * The maximum depth a page can have to be included when rendering
     *
     * @var int
     */
    protected $maxDepth;

    /**
     * Indentation string
     *
     * @var string
     */
    protected $indent = '';

    /**
     * ACL to use when iterating pages
     *
     * @var Acl\AclInterface
     */
    protected $acl;

    /**
     * Whether invisible items should be rendered by this helper
     *
     * @var bool
     */
    protected $renderInvisible = false;

    /**
     * ACL role to use when iterating pages
     *
     * @var string|Acl\Role\RoleInterface
     */
    protected $role;

    /**
     * Whether ACL should be used for filtering out pages
     *
     * @var bool
     */
    protected $useAcl = true;

    /**
     * Translator (optional)
     *
     * @var Translator
     */
    protected $translator;

    /**
     * Translator text domain (optional)
     *
     * @var string
     */
    protected $translatorTextDomain = 'default';

    /**
     * Whether translator should be used
     *
     * @var bool
     */
    protected $translatorEnabled = true;

    /**
     * Default ACL to use when iterating pages if not explicitly set in the
     * instance by calling {@link setAcl()}
     *
     * @var Acl\AclInterface
     */
    protected static $defaultAcl;

    /**
     * Default ACL role to use when iterating pages if not explicitly set in the
     * instance by calling {@link setRole()}
     *
     * @var string|Acl\Role\RoleInterface
     */
    protected static $defaultRole;

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param  string $method    method name in container
     * @param  array  $arguments rguments to pass
     * @return mixed
     * @throws Navigation\Exception\ExceptionInterface
     */
    public function __call($method, array $arguments = array())
    {
        return call_user_func_array(
            array($this->getContainer(), $method),
            $arguments
        );
    }

    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link HelperInterface::__toString()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
            return '';
        }
    }

    /**
     * Finds the deepest active page in the given container
     *
     * @param  Navigation\AbstractContainer $container  container to search
     * @param  int|null             $minDepth   [optional] minimum depth
     *                                          required for page to be
     *                                          valid. Default is to use
     *                                          {@link getMinDepth()}. A
     *                                          null value means no minimum
     *                                          depth required.
     * @param  int|null             $maxDepth   [optional] maximum depth
     *                                          a page can have to be
     *                                          valid. Default is to use
     *                                          {@link getMaxDepth()}. A
     *                                          null value means no maximum
     *                                          depth required.
     * @return array                            an associative array with
     *                                          the values 'depth' and
     *                                          'page', or an empty array
     *                                          if not found
     */
    public function findActive($container, $minDepth = null, $maxDepth = -1)
    {
        $this->parseContainer($container);
        if (!is_int($minDepth)) {
            $minDepth = $this->getMinDepth();
        }
        if ((!is_int($maxDepth) || $maxDepth < 0) && null !== $maxDepth) {
            $maxDepth = $this->getMaxDepth();
        }

        $found  = null;
        $foundDepth = -1;
        $iterator = new RecursiveIteratorIterator(
            $container,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \Zend\Navigation\Page\AbstractPage $page */
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
                if (!$found instanceof AbstractPage) {
                    $found = null;
                    break;
                }
            }
        }

        if ($found) {
            return array('page' => $found, 'depth' => $foundDepth);
        }

        return array();
    }

    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param  Navigation\AbstractContainer|string|null $container
     * @throws Exception\InvalidArgumentException
     */
    protected function parseContainer(&$container = null)
    {
        if (null === $container) {
            return;
        }

        if (is_string($container)) {
            if (!$this->getServiceLocator()) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Attempted to set container with alias "%s" but no ServiceLocator was set',
                    $container
                ));
            }

            /**
             * Load the navigation container from the root service locator
             *
             * The navigation container is probably located in Zend\ServiceManager\ServiceManager
             * and not in the View\HelperPluginManager. If the set service locator is a
             * HelperPluginManager, access the navigation container via the main service locator.
             */
            $sl = $this->getServiceLocator();
            if ($sl instanceof View\HelperPluginManager) {
                $sl = $sl->getServiceLocator();
            }
            $container = $sl->get($container);
            return;
        }

        if (!$container instanceof Navigation\AbstractContainer) {
            throw new  Exception\InvalidArgumentException(
                'Container must be a string alias or an instance of '
                . 'Zend\Navigation\AbstractContainer'
            );
        }
    }

    // Iterator filter methods:

    /**
     * Determines whether a page should be accepted when iterating
     *
     * Default listener may be 'overridden' by attaching listener to 'isAllowed'
     * method. Listener must be 'short circuited' if overriding default ACL
     * listener.
     *
     * Rules:
     * - If a page is not visible it is not accepted, unless RenderInvisible has
     *   been set to true
     * - If $useAcl is true (default is true):
     *      - Page is accepted if listener returns true, otherwise false
     * - If page is accepted and $recursive is true, the page
     *   will not be accepted if it is the descendant of a non-accepted page
     *
     * @param   AbstractPage    $page       page to check
     * @param   bool            $recursive  [optional] if true, page will not be
     *                                      accepted if it is the descendant of
     *                                      a page that is not accepted. Default
     *                                      is true
     *
     * @return  bool                        Whether page should be accepted
     */
    public function accept(AbstractPage $page, $recursive = true)
    {
        $accept = true;

        if (!$page->isVisible(false) && !$this->getRenderInvisible()) {
            $accept = false;
        } elseif ($this->getUseAcl()) {
            $acl = $this->getAcl();
            $role = $this->getRole();
            $params = array('acl' => $acl, 'page' => $page, 'role' => $role);
            $accept = $this->isAllowed($params);
        }

        if ($accept && $recursive) {
            $parent = $page->getParent();

            if ($parent instanceof AbstractPage) {
                $accept = $this->accept($parent, true);
            }
        }

        return $accept;
    }

    /**
     * Determines whether a page should be allowed given certain parameters
     *
     * @param   array   $params
     * @return  bool
     */
    protected function isAllowed($params)
    {
        $results = $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
        return $results->last();
    }

    // Util methods:

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param  int|string $indent
     * @return string
     */
    protected function getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * Overloads {@link View\Helper\AbstractHtmlElement::htmlAttribs()}.
     *
     * @param  array $attribs  an array where each key-value pair is converted
     *                         to an attribute name and value
     * @return string
     */
    protected function htmlAttribs($attribs)
    {
        // filter out null values and empty string values
        foreach ($attribs as $key => $value) {
            if ($value === null || (is_string($value) && !strlen($value))) {
                unset($attribs[$key]);
            }
        }

        return parent::htmlAttribs($attribs);
    }

    /**
     * Returns an HTML string containing an 'a' element for the given page
     *
     * @param  AbstractPage $page  page to generate HTML for
     * @return string              HTML string (<a href="…">Label</a>)
     */
    public function htmlify(AbstractPage $page)
    {
        $label = $this->translate($page->getLabel(), $page->getTextDomain());
        $title = $this->translate($page->getTitle(), $page->getTextDomain());

        // get attribs for anchor element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $title,
            'class'  => $page->getClass(),
            'href'   => $page->getHref(),
            'target' => $page->getTarget()
        );

        /** @var \Zend\View\Helper\EscapeHtml $escaper */
        $escaper = $this->view->plugin('escapeHtml');
        $label   = $escaper($label);

        return '<a' . $this->htmlAttribs($attribs) . '>' . $label . '</a>';
    }

    /**
     * Translate a message (for label, title, …)
     *
     * @param  string $message    ID of the message to translate
     * @param  string $textDomain Text domain (category name for the translations)
     * @return string             Translated message
     */
    protected function translate($message, $textDomain = null)
    {
        if (is_string($message) && !empty($message)) {
            if (null !== ($translator = $this->getTranslator())) {
                if (null === $textDomain) {
                    $textDomain = $this->getTranslatorTextDomain();
                }

                return $translator->translate($message, $textDomain);
            }
        }

        return $message;
    }

    /**
     * Normalize an ID
     *
     * Overrides {@link View\Helper\AbstractHtmlElement::normalizeId()}.
     *
     * @param  string $value
     * @return string
     */
    protected function normalizeId($value)
    {
        $prefix = get_class($this);
        $prefix = strtolower(trim(substr($prefix, strrpos($prefix, '\\')), '\\'));

        return $prefix . '-' . $value;
    }

    /**
     * Sets ACL to use when iterating pages
     *
     * Implements {@link HelperInterface::setAcl()}.
     *
     * @param  Acl\AclInterface $acl ACL object.
     * @return AbstractHelper
     */
    public function setAcl(Acl\AclInterface $acl = null)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * Returns ACL or null if it isn't set using {@link setAcl()} or
     * {@link setDefaultAcl()}
     *
     * Implements {@link HelperInterface::getAcl()}.
     *
     * @return Acl\AclInterface|null  ACL object or null
     */
    public function getAcl()
    {
        if ($this->acl === null && static::$defaultAcl !== null) {
            return static::$defaultAcl;
        }

        return $this->acl;
    }

    /**
     * Checks if the helper has an ACL instance
     *
     * Implements {@link HelperInterface::hasAcl()}.
     *
     * @return bool
     */
    public function hasAcl()
    {
        if ($this->acl instanceof Acl\Acl
            || static::$defaultAcl instanceof Acl\Acl
        ) {
            return true;
        }

        return false;
    }

    /**
     * Set the event manager.
     *
     * @param   EventManagerInterface $events
     * @return  AbstractHelper
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));

        $this->events = $events;

        $this->setDefaultListeners();

        return $this;
    }

    /**
     * Get the event manager.
     *
     * @return  EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Sets navigation container the helper operates on by default
     *
     * Implements {@link HelperInterface::setContainer()}.
     *
     * @param  string|Navigation\AbstractContainer $container Default is null, meaning container will be reset.
     * @return AbstractHelper
     */
    public function setContainer($container = null)
    {
        $this->parseContainer($container);
        $this->container = $container;

        return $this;
    }

    /**
     * Returns the navigation container helper operates on by default
     *
     * Implements {@link HelperInterface::getContainer()}.
     *
     * If no container is set, a new container will be instantiated and
     * stored in the helper.
     *
     * @return Navigation\AbstractContainer  navigation container
     */
    public function getContainer()
    {
        if (null === $this->container) {
            $this->container = new Navigation\Navigation();
        }

        return $this->container;
    }

    /**
     * Checks if the helper has a container
     *
     * Implements {@link HelperInterface::hasContainer()}.
     *
     * @return bool
     */
    public function hasContainer()
    {
        return null !== $this->container;
    }

    /**
     * Set the indentation string for using in {@link render()}, optionally a
     * number of spaces to indent with
     *
     * @param  string|int $indent
     * @return AbstractHelper
     */
    public function setIndent($indent)
    {
        $this->indent = $this->getWhitespace($indent);
        return $this;
    }

    /**
     * Returns indentation
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Sets the maximum depth a page can have to be included when rendering
     *
     * @param  int $maxDepth Default is null, which sets no maximum depth.
     * @return AbstractHelper
     */
    public function setMaxDepth($maxDepth = null)
    {
        if (null === $maxDepth || is_int($maxDepth)) {
            $this->maxDepth = $maxDepth;
        } else {
            $this->maxDepth = (int) $maxDepth;
        }

        return $this;
    }

    /**
     * Returns maximum depth a page can have to be included when rendering
     *
     * @return int|null
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * Sets the minimum depth a page must have to be included when rendering
     *
     * @param  int $minDepth Default is null, which sets no minimum depth.
     * @return AbstractHelper
     */
    public function setMinDepth($minDepth = null)
    {
        if (null === $minDepth || is_int($minDepth)) {
            $this->minDepth = $minDepth;
        } else {
            $this->minDepth = (int) $minDepth;
        }

        return $this;
    }

    /**
     * Returns minimum depth a page must have to be included when rendering
     *
     * @return int|null
     */
    public function getMinDepth()
    {
        if (!is_int($this->minDepth) || $this->minDepth < 0) {
            return 0;
        }

        return $this->minDepth;
    }

    /**
     * Render invisible items?
     *
     * @param  bool $renderInvisible
     * @return AbstractHelper
     */
    public function setRenderInvisible($renderInvisible = true)
    {
        $this->renderInvisible = (bool) $renderInvisible;
        return $this;
    }

    /**
     * Return renderInvisible flag
     *
     * @return bool
     */
    public function getRenderInvisible()
    {
        return $this->renderInvisible;
    }

    /**
     * Sets ACL role(s) to use when iterating pages
     *
     * Implements {@link HelperInterface::setRole()}.
     *
     * @param  mixed $role [optional] role to set. Expects a string, an
     *                     instance of type {@link Acl\Role\RoleInterface}, or null. Default
     *                     is null, which will set no role.
     * @return AbstractHelper
     * @throws Exception\InvalidArgumentException
     */
    public function setRole($role = null)
    {
        if (null === $role || is_string($role) ||
            $role instanceof Acl\Role\RoleInterface
        ) {
            $this->role = $role;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                '$role must be a string, null, or an instance of '
                . 'Zend\Permissions\Role\RoleInterface; %s given',
                (is_object($role) ? get_class($role) : gettype($role))
            ));
        }

        return $this;
    }

    /**
     * Returns ACL role to use when iterating pages, or null if it isn't set
     * using {@link setRole()} or {@link setDefaultRole()}
     *
     * Implements {@link HelperInterface::getRole()}.
     *
     * @return string|Acl\Role\RoleInterface|null
     */
    public function getRole()
    {
        if ($this->role === null && static::$defaultRole !== null) {
            return static::$defaultRole;
        }

        return $this->role;
    }

    /**
     * Checks if the helper has an ACL role
     *
     * Implements {@link HelperInterface::hasRole()}.
     *
     * @return bool
     */
    public function hasRole()
    {
        if ($this->role instanceof Acl\Role\RoleInterface
            || is_string($this->role)
            || static::$defaultRole instanceof Acl\Role\RoleInterface
            || is_string(static::$defaultRole)
        ) {
            return true;
        }

        return false;
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

    // Translator methods - Good candidate to refactor as a trait with PHP 5.4

    /**
     * Sets translator to use in helper
     *
     * @param  Translator $translator  [optional] translator.
     *                                 Default is null, which sets no translator.
     * @param  string     $textDomain  [optional] text domain
     *                                 Default is null, which skips setTranslatorTextDomain
     * @return AbstractHelper
     */
    public function setTranslator(Translator $translator = null, $textDomain = null)
    {
        $this->translator = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    /**
     * Returns translator used in helper
     *
     * @return Translator|null
     */
    public function getTranslator()
    {
        if (! $this->isTranslatorEnabled()) {
            return;
        }

        return $this->translator;
    }

    /**
     * Checks if the helper has a translator
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool) $this->getTranslator();
    }

    /**
     * Sets whether translator is enabled and should be used
     *
     * @param  bool $enabled
     * @return AbstractHelper
     */
    public function setTranslatorEnabled($enabled = true)
    {
        $this->translatorEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * Returns whether translator is enabled and should be used
     *
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        return $this->translatorEnabled;
    }

    /**
     * Set translation text domain
     *
     * @param  string $textDomain
     * @return AbstractHelper
     */
    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->translatorTextDomain = $textDomain;
        return $this;
    }

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain()
    {
        return $this->translatorTextDomain;
    }

    /**
     * Sets whether ACL should be used
     *
     * Implements {@link HelperInterface::setUseAcl()}.
     *
     * @param  bool $useAcl
     * @return AbstractHelper
     */
    public function setUseAcl($useAcl = true)
    {
        $this->useAcl = (bool) $useAcl;
        return $this;
    }

    /**
     * Returns whether ACL should be used
     *
     * Implements {@link HelperInterface::getUseAcl()}.
     *
     * @return bool
     */
    public function getUseAcl()
    {
        return $this->useAcl;
    }

    // Static methods:

    /**
     * Sets default ACL to use if another ACL is not explicitly set
     *
     * @param  Acl\AclInterface $acl [optional] ACL object. Default is null, which
     *                      sets no ACL object.
     * @return void
     */
    public static function setDefaultAcl(Acl\AclInterface $acl = null)
    {
        static::$defaultAcl = $acl;
    }

    /**
     * Sets default ACL role(s) to use when iterating pages if not explicitly
     * set later with {@link setRole()}
     *
     * @param  mixed $role [optional] role to set. Expects null, string, or an
     *                     instance of {@link Acl\Role\RoleInterface}. Default is null, which
     *                     sets no default role.
     * @return void
     * @throws Exception\InvalidArgumentException if role is invalid
     */
    public static function setDefaultRole($role = null)
    {
        if (null === $role
            || is_string($role)
            || $role instanceof Acl\Role\RoleInterface
        ) {
            static::$defaultRole = $role;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                '$role must be null|string|Zend\Permissions\Role\RoleInterface; received "%s"',
                (is_object($role) ? get_class($role) : gettype($role))
            ));
        }
    }

    /**
     * Attaches default ACL listeners, if ACLs are in use
     */
    protected function setDefaultListeners()
    {
        if (!$this->getUseAcl()) {
            return;
        }

        $this->getEventManager()->getSharedManager()->attach(
            'Zend\View\Helper\Navigation\AbstractHelper',
            'isAllowed',
            array('Zend\View\Helper\Navigation\Listener\AclListener', 'accept')
        );
    }
}
