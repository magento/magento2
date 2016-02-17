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
 * Helper for rendering menus from navigation containers
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Navigation_Menu
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**
     * CSS class to use for the ul element
     *
     * @var string
     */
    protected $_ulClass = 'navigation';

    /**
     * Unique identifier (id) for the ul element
     *
     * @var string
     */
    protected $_ulId = null;

    /**
     * CSS class to use for the active elements
     *
     * @var string
     */
    protected $_activeClass = 'active';

    /**
     * CSS class to use for the parent li element
     *
     * @var string
     */
    protected $_parentClass = 'menu-parent';

    /**
     * Whether parent li elements should be rendered with parent class
     *
     * @var bool
     */
    protected $_renderParentClass = false;

    /**
     * Whether only active branch should be rendered
     *
     * @var bool
     */
    protected $_onlyActiveBranch = false;

    /**
     * Whether parents should be rendered when only rendering active branch
     *
     * @var bool
     */
    protected $_renderParents = true;

    /**
     * Partial view script to use for rendering menu
     *
     * @var string|array
     */
    protected $_partial = null;

    /**
     * Expand all sibling nodes of active branch nodes
     *
     * @var bool
     */
    protected $_expandSiblingNodesOfActiveBranch = false;

    /**
     * Adds CSS class from page to li element
     *
     * @var bool
     */
    protected $_addPageClassToLi = false;

    /**
     * Inner indentation string
     *
     * @var string
     */
    protected $_innerIndent = '    ';

    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation_Menu      fluent interface,
     *                                               returns self
     */
    public function menu(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets CSS class to use for the first 'ul' element when rendering
     *
     * @param  string $ulClass                   CSS class to set
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setUlClass($ulClass)
    {
        if (is_string($ulClass)) {
            $this->_ulClass = $ulClass;
        }

        return $this;
    }

    /**
     * Returns CSS class to use for the first 'ul' element when rendering
     *
     * @return string  CSS class
     */
    public function getUlClass()
    {
        return $this->_ulClass;
    }

    /**
     * Sets unique identifier (id) to use for the first 'ul' element when
     * rendering
     *
     * @param  string|null  $ulId                Unique identifier (id) to set
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setUlId($ulId)
    {
        if (is_string($ulId)) {
            $this->_ulId = $ulId;
        }

        return $this;
    }

    /**
     * Returns unique identifier (id) to use for the first 'ul' element when
     * rendering
     *
     * @return string|null  Unique identifier (id); Default is 'null'
     */
    public function getUlId()
    {
        return $this->_ulId;
    }

    /**
     * Sets CSS class to use for the active elements when rendering
     *
     * @param string $activeClass               CSS class to set
     * @return Zend_View_Helper_Navigation_Menu fluent interface, returns self
     */
    public function setActiveClass($activeClass)
    {
        if (is_string($activeClass)) {
            $this->_activeClass = $activeClass;
        }

        return $this;
    }

    /**
     * Returns CSS class to use for the active elements when rendering
     *
     * @return string  CSS class
     */
    public function getActiveClass()
    {
        return $this->_activeClass;
    }

    /**
     * Sets CSS class to use for the parent li elements when rendering
     *
     * @param  string $parentClass              CSS class to set to parents
     * @return Zend_View_Helper_Navigation_Menu fluent interface, returns self
     */
    public function setParentClass($parentClass)
    {
        if (is_string($parentClass)) {
            $this->_parentClass = $parentClass;
        }

        return $this;
    }

    /**
     * Returns CSS class to use for the parent lie elements when rendering
     *
     * @return string CSS class
     */
    public function getParentClass()
    {
        return $this->_parentClass;
    }

    /**
     * Enables/disables rendering of parent class to the li element
     *
     * @param bool $flag                        [optional] render with parent
     *                                          class. Default is true.
     * @return Zend_View_Helper_Navigation_Menu fluent interface, returns self
     */
    public function setRenderParentClass($flag = true)
    {
        $this->_renderParentClass = (bool) $flag;
        return $this;
    }

    /**
     * Returns flag indicating whether parent class should be rendered to the li
     * element
     *
     * @return bool  whether parent class should be rendered
     */
    public function getRenderParentClass()
    {
        return $this->_renderParentClass;
    }

    /**
     * Sets a flag indicating whether only active branch should be rendered
     *
     * @param  bool $flag                        [optional] render only active
     *                                           branch. Default is true.
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setOnlyActiveBranch($flag = true)
    {
        $this->_onlyActiveBranch = (bool) $flag;
        return $this;
    }

    /**
     * Returns a flag indicating whether only active branch should be rendered
     *
     * By default, this value is false, meaning the entire menu will be
     * be rendered.
     *
     * @return bool  whether only active branch should be rendered
     */
    public function getOnlyActiveBranch()
    {
        return $this->_onlyActiveBranch;
    }

    /**
     * Sets a flag indicating whether to expand all sibling nodes of the active branch
     *
     * @param  bool $flag                        [optional] expand all siblings of
     *                                           nodes in the active branch. Default is true.
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setExpandSiblingNodesOfActiveBranch($flag = true)
    {
        $this->_expandSiblingNodesOfActiveBranch = (bool) $flag;
        return $this;
    }

    /**
     * Returns a flag indicating whether to expand all sibling nodes of the active branch
     *
     * By default, this value is false, meaning the entire menu will be
     * be rendered.
     *
     * @return bool  whether siblings of nodes in the active branch should be expanded
     */
    public function getExpandSiblingNodesOfActiveBranch()
    {
        return $this->_expandSiblingNodesOfActiveBranch;
    }

    /**
     * Enables/disables rendering of parents when only rendering active branch
     *
     * See {@link setOnlyActiveBranch()} for more information.
     *
     * @param  bool $flag                        [optional] render parents when
     *                                           rendering active branch.
     *                                           Default is true.
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setRenderParents($flag = true)
    {
        $this->_renderParents = (bool) $flag;
        return $this;
    }

    /**
     * Returns flag indicating whether parents should be rendered when rendering
     * only the active branch
     *
     * By default, this value is true.
     *
     * @return bool  whether parents should be rendered
     */
    public function getRenderParents()
    {
        return $this->_renderParents;
    }

    /**
     * Sets which partial view script to use for rendering menu
     *
     * @param  string|array $partial             partial view script or null. If
     *                                           an array is given, it is
     *                                           expected to contain two values;
     *                                           the partial view script to use,
     *                                           and the module where the script
     *                                           can be found.
     * @return Zend_View_Helper_Navigation_Menu  fluent interface, returns self
     */
    public function setPartial($partial)
    {
        if (null === $partial || is_string($partial) || is_array($partial)) {
            $this->_partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu
     *
     * @return string|array|null
     */
    public function getPartial()
    {
        return $this->_partial;
    }

    /**
     * Adds CSS class from page to li element
     *
     * Before:
     * <code>
     * <li>
     *     <a href="#" class="foo">Bar</a>
     * </li>
     * </code>
     *
     * After:
     * <code>
     * <li class="foo">
     *     <a href="#">Bar</a>
     * </li>
     * </code>
     *
     * @param bool $flag                        [optional] adds CSS class from
     *                                          page to li element
     *
     * @return Zend_View_Helper_Navigation_Menu fluent interface, returns self
     */
    public function addPageClassToLi($flag = true)
    {
        $this->_addPageClassToLi = (bool) $flag;

        return $this;
    }

    /**
     * Returns a flag indicating whether the CSS class from page to be added to
     * li element
     *
     * @return bool
     */
    public function getAddPageClassToLi()
    {
        return $this->_addPageClassToLi;
    }

    /**
     * Set the inner indentation string for using in {@link render()}, optionally
     * a number of spaces to indent with
     *
     * @param  string|int $indent                          indentation string or
     *                                                     number of spaces
     * @return Zend_View_Helper_Navigation_HelperAbstract  fluent interface,
     *                                                     returns self
     */
    public function setInnerIndent($indent)
    {
        $this->_innerIndent = $this->_getWhitespace($indent);

        return $this;
    }

    /**
     * Returns inner indentation (format output is respected)
     *
     * @see getFormatOutput()
     *
     * @return string       indentation string or an empty string
     */
    public function getInnerIndent()
    {
        if (false === $this->getFormatOutput()) {
            return '';
        }

        return $this->_innerIndent;
    }

    // Public methods:

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty
     *
     * Overrides {@link Zend_View_Helper_Navigation_Abstract::htmlify()}.
     *
     * @param  Zend_Navigation_Page $page  page to generate HTML for
     * @return string                      HTML string for the given page
     */
    public function htmlify(Zend_Navigation_Page $page)
    {
        // get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();

        // translate label and title?
        if ($this->getUseTranslator() && $t = $this->getTranslator()) {
            if (is_string($label) && !empty($label)) {
                $label = $t->translate($label);
            }
            if (is_string($title) && !empty($title)) {
                $title = $t->translate($title);
            }
        }

        // get attribs for element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $title,
        );

        if (false === $this->getAddPageClassToLi()) {
            $attribs['class'] = $page->getClass();
        }

        // does page have a href?
        if ($href = $page->getHref()) {
            $element              = 'a';
            $attribs['href']      = $href;
            $attribs['target']    = $page->getTarget();
            $attribs['accesskey'] = $page->getAccessKey();
        } else {
            $element = 'span';
        }

        // Add custom HTML attributes
        $attribs = array_merge($attribs, $page->getCustomHtmlAttribs());

        return '<' . $element . $this->_htmlAttribs($attribs) . '>'
             . $this->view->escape($label)
             . '</' . $element . '>';
    }

    /**
     * Normalizes given render options
     *
     * @param  array $options  [optional] options to normalize
     * @return array           normalized options
     */
    protected function _normalizeOptions(array $options = array())
    {
        // Ident
        if (isset($options['indent'])) {
            $options['indent'] = $this->_getWhitespace($options['indent']);
        } else {
            $options['indent'] = $this->getIndent();
        }

        // Inner ident
        if (isset($options['innerIndent'])) {
            $options['innerIndent'] =
                $this->_getWhitespace($options['innerIndent']);
        } else {
            $options['innerIndent'] = $this->getInnerIndent();
        }

        // UL class
        if (isset($options['ulClass']) && $options['ulClass'] !== null) {
            $options['ulClass'] = (string) $options['ulClass'];
        } else {
            $options['ulClass'] = $this->getUlClass();
        }

        // UL id
        if (isset($options['ulId']) && $options['ulId'] !== null) {
            $options['ulId'] = (string) $options['ulId'];
        } else {
            $options['ulId'] = $this->getUlId();
        }

        // Active class
        if (isset($options['activeClass']) && $options['activeClass'] !== null
        ) {
            $options['activeClass'] = (string) $options['activeClass'];
        } else {
            $options['activeClass'] = $this->getActiveClass();
        }

        // Parent class
        if (isset($options['parentClass']) && $options['parentClass'] !== null) {
            $options['parentClass'] = (string) $options['parentClass'];
        } else {
            $options['parentClass'] = $this->getParentClass();
        }

        // Minimum depth
        if (array_key_exists('minDepth', $options)) {
            if (null !== $options['minDepth']) {
                $options['minDepth'] = (int) $options['minDepth'];
            }
        } else {
            $options['minDepth'] = $this->getMinDepth();
        }

        if ($options['minDepth'] < 0 || $options['minDepth'] === null) {
            $options['minDepth'] = 0;
        }

        // Maximum depth
        if (array_key_exists('maxDepth', $options)) {
            if (null !== $options['maxDepth']) {
                $options['maxDepth'] = (int) $options['maxDepth'];
            }
        } else {
            $options['maxDepth'] = $this->getMaxDepth();
        }

        // Only active branch
        if (!isset($options['onlyActiveBranch'])) {
            $options['onlyActiveBranch'] = $this->getOnlyActiveBranch();
        }

        // Expand sibling nodes of active branch
        if (!isset($options['expandSiblingNodesOfActiveBranch'])) {
            $options['expandSiblingNodesOfActiveBranch'] = $this->getExpandSiblingNodesOfActiveBranch();
        }

        // Render parents?
        if (!isset($options['renderParents'])) {
            $options['renderParents'] = $this->getRenderParents();
        }

        // Render parent class?
        if (!isset($options['renderParentClass'])) {
            $options['renderParentClass'] = $this->getRenderParentClass();
        }

        // Add page CSS class to LI element
        if (!isset($options['addPageClassToLi'])) {
            $options['addPageClassToLi'] = $this->getAddPageClassToLi();
        }

        return $options;
    }

    // Render methods:

    /**
     * Renders the deepest active menu within [$minDepth, $maxDeth], (called
     * from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container     container to render
     * @param  string                    $ulClass       CSS class for first UL
     * @param  string                    $indent        initial indentation
     * @param  string                    $innerIndent   inner indentation
     * @param  int|null                  $minDepth      minimum depth
     * @param  int|null                  $maxDepth      maximum depth
     * @param  string|null               $ulId          unique identifier (id)
     *                                                  for first UL
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     * @param  string|null               $activeClass       CSS class for active
     *                                                      element
     * @param  string                    $parentClass       CSS class for parent
     *                                                      li's
     * @param  bool                      $renderParentClass Render parent class?
     * @return string                                       rendered menu (HTML)
     */
    protected function _renderDeepestMenu(Zend_Navigation_Container $container,
                                          $ulClass,
                                          $indent,
                                          $innerIndent,
                                          $minDepth,
                                          $maxDepth,
                                          $ulId,
                                          $addPageClassToLi,
                                          $activeClass,
                                          $parentClass,
                                          $renderParentClass)
    {
        if (!$active = $this->findActive($container, $minDepth - 1, $maxDepth)) {
            return '';
        }

        // special case if active page is one below minDepth
        if ($active['depth'] < $minDepth) {
            if (!$active['page']->hasPages()) {
                return '';
            }
        } else if (!$active['page']->hasPages()) {
            // found pages has no children; render siblings
            $active['page'] = $active['page']->getParent();
        } else if (is_int($maxDepth) && $active['depth'] + 1 > $maxDepth) {
            // children are below max depth; render siblings
            $active['page'] = $active['page']->getParent();
        }

        $attribs = array(
            'class' => $ulClass,
            'id'    => $ulId,
        );

        // We don't need a prefix for the menu ID (backup)
        $skipValue = $this->_skipPrefixForId;
        $this->skipPrefixForId();

        $html = $indent . '<ul'
                        . $this->_htmlAttribs($attribs)
                        . '>'
                        . $this->getEOL();

        // Reset prefix for IDs
        $this->_skipPrefixForId = $skipValue;

        foreach ($active['page'] as $subPage) {
            if (!$this->accept($subPage)) {
                continue;
            }

            $liClass = '';
            if ($subPage->isActive(true) && $addPageClassToLi) {
                $liClass = $this->_htmlAttribs(
                    array('class' => $activeClass . ' ' . $subPage->getClass())
                );
            } else if ($subPage->isActive(true)) {
                $liClass = $this->_htmlAttribs(array('class' => $activeClass));
            } else if ($addPageClassToLi) {
                $liClass = $this->_htmlAttribs(
                    array('class' => $subPage->getClass())
                );
            }
            $html .= $indent . $innerIndent . '<li' . $liClass . '>' . $this->getEOL();
            $html .= $indent . str_repeat($innerIndent, 2) . $this->htmlify($subPage)
                                                           . $this->getEOL();
            $html .= $indent . $innerIndent . '</li>' . $this->getEOL();
        }

        $html .= $indent . '</ul>';

        return $html;
    }

    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container     container to render
     * @param  string                    $ulClass       CSS class for first UL
     * @param  string                    $indent        initial indentation
     * @param  string                    $innerIndent   inner indentation
     * @param  int|null                  $minDepth      minimum depth
     * @param  int|null                  $maxDepth      maximum depth
     * @param  bool                      $onlyActive    render only active branch?
     * @param  bool                      $expandSibs    render siblings of active
     *                                                  branch nodes?
     * @param  string|null               $ulId          unique identifier (id)
     *                                                  for first UL
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     * @param  string|null               $activeClass       CSS class for active
     *                                                      element
     * @param  string                    $parentClass       CSS class for parent
     *                                                      li's
     * @param  bool                      $renderParentClass Render parent class?
     * @return string                                       rendered menu (HTML)
     */
    protected function _renderMenu(Zend_Navigation_Container $container,
                                   $ulClass,
                                   $indent,
                                   $innerIndent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $expandSibs,
                                   $ulId,
                                   $addPageClassToLi,
                                   $activeClass,
                                   $parentClass,
                                   $renderParentClass)
    {
        $html = '';

        // find deepest active
        if ($found = $this->findActive($container, $minDepth, $maxDepth)) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
                            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach ($iterator as $page) {
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if ($expandSibs && $depth > $minDepth) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($page->getParent()->isActive(true)) {
                        // page is a sibling of the active branch...
                        $accept = true;
                    }
                }
                if (!$isActive && !$accept) {
                    continue;
                }
            } else if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth   -= $minDepth;
            $myIndent = $indent . str_repeat($innerIndent, $depth * 2);

            if ($depth > $prevDepth) {
                $attribs = array();

                // start new ul tag
                if (0 == $depth) {
                    $attribs = array(
                        'class' => $ulClass,
                        'id'    => $ulId,
                    );
                }

                // We don't need a prefix for the menu ID (backup)
                $skipValue = $this->_skipPrefixForId;
                $this->skipPrefixForId();

                $html .= $myIndent . '<ul'
                                   . $this->_htmlAttribs($attribs)
                                   . '>'
                                   . $this->getEOL();

                // Reset prefix for IDs
                $this->_skipPrefixForId = $skipValue;
            } else if ($prevDepth > $depth) {
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    $ind   = $indent . str_repeat($innerIndent, $i * 2);
                    $html .= $ind . $innerIndent . '</li>' . $this->getEOL();
                    $html .= $ind . '</ul>' . $this->getEOL();
                }
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            } else {
                // close previous li tag
                $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
            }

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($isActive) {
                $liClasses[] = $activeClass;
            }
            // Add CSS class from page to LI?
            if ($addPageClassToLi) {
                $liClasses[] = $page->getClass();
            }
            // Add CSS class for parents to LI?
            if ($renderParentClass && $page->hasChildren()) {
                // Check max depth
                if ((is_int($maxDepth) && ($depth + 1 < $maxDepth))
                    || !is_int($maxDepth)
                ) {
                    $liClasses[] = $parentClass;
                }
            }

            $html .= $myIndent . $innerIndent . '<li'
                   . $this->_htmlAttribs(array('class' => implode(' ', $liClasses)))
                   . '>' . $this->getEOL()
                   . $myIndent . str_repeat($innerIndent, 2)
                   . $this->htmlify($page)
                   . $this->getEOL();

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            // done iterating container; close open ul/li tags
            for ($i = $prevDepth+1; $i > 0; $i--) {
                $myIndent = $indent . str_repeat($innerIndent . $innerIndent, $i - 1);
                $html    .= $myIndent . $innerIndent . '</li>' . $this->getEOL()
                         . $myIndent . '</ul>' . $this->getEOL();
            }
            $html = rtrim($html, $this->getEOL());
        }

        return $html;
    }

    /**
     * Renders helper
     *
     * Renders a HTML 'ul' for the given $container. If $container is not given,
     * the container registered in the helper will be used.
     *
     * Available $options:
     *
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               create menu from. Default
     *                                               is to use the container
     *                                               retrieved from
     *                                               {@link getContainer()}.
     * @param  array                     $options    [optional] options for
     *                                               controlling rendering
     * @return string                                rendered menu
     */
    public function renderMenu(Zend_Navigation_Container $container = null,
                               array $options = array())
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        $options = $this->_normalizeOptions($options);

        if ($options['onlyActiveBranch'] && !$options['renderParents']) {
            $html = $this->_renderDeepestMenu(
                $container,
                $options['ulClass'],
                $options['indent'],
                $options['innerIndent'],
                $options['minDepth'],
                $options['maxDepth'],
                $options['ulId'],
                $options['addPageClassToLi'],
                $options['activeClass'],
                $options['parentClass'],
                $options['renderParentClass']
            );
        } else {
            $html = $this->_renderMenu(
                $container,
                $options['ulClass'],
                $options['indent'],
                $options['innerIndent'],
                $options['minDepth'],
                $options['maxDepth'],
                $options['onlyActiveBranch'],
                $options['expandSiblingNodesOfActiveBranch'],
                $options['ulId'],
                $options['addPageClassToLi'],
                $options['activeClass'],
                $options['parentClass'],
                $options['renderParentClass']
            );
        }

        return $html;
    }

    /**
     * Renders the inner-most sub menu for the active page in the $container
     *
     * This is a convenience method which is equivalent to the following call:
     * <code>
     * renderMenu($container, array(
     *     'indent'           => $indent,
     *     'ulClass'          => $ulClass,
     *     'minDepth'         => null,
     *     'maxDepth'         => null,
     *     'onlyActiveBranch' => true,
     *     'renderParents'    => false
     * ));
     * </code>
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to render
     *                                               the container registered in
     *                                               the helper.
     * @param  string|null               $ulClass    [optional] CSS class to
     *                                               use for UL element. Default
     *                                               is to use the value from
     *                                               {@link getUlClass()}.
     * @param  string|int                $indent     [optional] indentation as
     *                                               a string or number of
     *                                               spaces. Default is to use
     *                                               the value retrieved from
     *                                               {@link getIndent()}.
     * @param  string|null               $ulId       [optional] Unique identifier
     *                                               (id) use for UL element
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     * @param  string|int                $innerIndent   [optional] inner
     *                                                  indentation as a string
     *                                                  or number of spaces.
     *                                                  Default is to use the
     *                                                  {@link getInnerIndent()}.
     * @return string                                   rendered content
     */
    public function renderSubMenu(Zend_Navigation_Container $container = null,
                                  $ulClass = null,
                                  $indent = null,
                                  $ulId   = null,
                                  $addPageClassToLi = false,
                                  $innerIndent = null)
    {
        return $this->renderMenu($container, array(
            'indent'           => $indent,
            'innerIndent'      => $innerIndent,
            'ulClass'          => $ulClass,
            'minDepth'         => null,
            'maxDepth'         => null,
            'onlyActiveBranch' => true,
            'renderParents'    => false,
            'ulId'             => $ulId,
            'addPageClassToLi' => $addPageClassToLi,
        ));
    }

    /**
     * Renders the given $container by invoking the partial view helper
     *
     * The container will simply be passed on as a model to the view script
     * as-is, and will be available in the partial script as 'container', e.g.
     * <code>echo 'Number of pages: ', count($this->container);</code>.
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               pass to view script. Default
     *                                               is to use the container
     *                                               registered in the helper.
     * @param  string|array             $partial     [optional] partial view
     *                                               script to use. Default is to
     *                                               use the partial registered
     *                                               in the helper. If an array
     *                                               is given, it is expected to
     *                                               contain two values; the
     *                                               partial view script to use,
     *                                               and the module where the
     *                                               script can be found.
     * @return string                                helper output
     *
     * @throws Zend_View_Exception   When no partial script is set
     */
    public function renderPartial(Zend_Navigation_Container $container = null,
                                  $partial = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(
                'Unable to render menu: No partial view script provided'
            );
            $e->setView($this->view);
            throw $e;
        }

        $model = array(
            'container' => $container
        );

        if (is_array($partial)) {
            if (count($partial) != 2) {
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(
                    'Unable to render menu: A view partial supplied as '
                    .  'an array must contain two values: partial view '
                    .  'script and module where script can be found'
                );
                $e->setView($this->view);
                throw $e;
            }

            return $this->view->partial($partial[0], $partial[1], $model);
        }

        return $this->view->partial($partial, null, $model);
    }

    // Zend_View_Helper_Navigation_Helper:

    /**
     * Renders menu
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::render()}.
     *
     * If a partial view is registered in the helper, the menu will be rendered
     * using the given partial script. If no partial is registered, the menu
     * will be rendered as an 'ul' element by the helper's internal method.
     *
     * @see renderPartial()
     * @see renderMenu()
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function render(Zend_Navigation_Container $container = null)
    {
        if ($partial = $this->getPartial()) {
            return $this->renderPartial($container, $partial);
        } else {
            return $this->renderMenu($container);
        }
    }
}
