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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Links.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_View_Helper_Navigation_HelperAbstract
 */
#require_once 'Zend/View/Helper/Navigation/HelperAbstract.php';

/**
 * Helper for printing <link> elements
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Navigation_Links
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**#@+
     * Constants used for specifying which link types to find and render
     *
     * @var int
     */
    const RENDER_ALTERNATE  = 0x0001;
    const RENDER_STYLESHEET = 0x0002;
    const RENDER_START      = 0x0004;
    const RENDER_NEXT       = 0x0008;
    const RENDER_PREV       = 0x0010;
    const RENDER_CONTENTS   = 0x0020;
    const RENDER_INDEX      = 0x0040;
    const RENDER_GLOSSARY   = 0x0080;
    const RENDER_COPYRIGHT  = 0x0100;
    const RENDER_CHAPTER    = 0x0200;
    const RENDER_SECTION    = 0x0400;
    const RENDER_SUBSECTION = 0x0800;
    const RENDER_APPENDIX   = 0x1000;
    const RENDER_HELP       = 0x2000;
    const RENDER_BOOKMARK   = 0x4000;
    const RENDER_CUSTOM     = 0x8000;
    const RENDER_ALL        = 0xffff;
    /**#@+**/

    /**
     * Maps render constants to W3C link types
     *
     * @var array
     */
    protected static $_RELATIONS = array(
        self::RENDER_ALTERNATE  => 'alternate',
        self::RENDER_STYLESHEET => 'stylesheet',
        self::RENDER_START      => 'start',
        self::RENDER_NEXT       => 'next',
        self::RENDER_PREV       => 'prev',
        self::RENDER_CONTENTS   => 'contents',
        self::RENDER_INDEX      => 'index',
        self::RENDER_GLOSSARY   => 'glossary',
        self::RENDER_COPYRIGHT  => 'copyright',
        self::RENDER_CHAPTER    => 'chapter',
        self::RENDER_SECTION    => 'section',
        self::RENDER_SUBSECTION => 'subsection',
        self::RENDER_APPENDIX   => 'appendix',
        self::RENDER_HELP       => 'help',
        self::RENDER_BOOKMARK   => 'bookmark'
    );

    /**
     * The helper's render flag
     *
     * @see render()
     * @see setRenderFlag()
     * @var int
     */
    protected $_renderFlag = self::RENDER_ALL;

    /**
     * Root container
     *
     * Used for preventing methods to traverse above the container given to
     * the {@link render()} method.
     *
     * @see _findRoot()
     *
     * @var Zend_Navigation_Container
     */
    protected $_root;

    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation_Links     fluent interface, returns
     *                                               self
     */
    public function links(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Magic overload: Proxy calls to {@link findRelation()} or container
     *
     * Examples of finder calls:
     * <code>
     * // METHOD                  // SAME AS
     * $h->findRelNext($page);    // $h->findRelation($page, 'rel', 'next')
     * $h->findRevSection($page); // $h->findRelation($page, 'rev', 'section');
     * $h->findRelFoo($page);     // $h->findRelation($page, 'rel', 'foo');
     * </code>
     *
     * @param  string $method             method name
     * @param  array  $arguments          method arguments
     * @throws Zend_Navigation_Exception  if method does not exist in container
     */
    public function __call($method, array $arguments = array())
    {
        if (@preg_match('/find(Rel|Rev)(.+)/', $method, $match)) {
            return $this->findRelation($arguments[0],
                                       strtolower($match[1]),
                                       strtolower($match[2]));
        }

        return parent::__call($method, $arguments);
    }

    // Accessors:

    /**
     * Sets the helper's render flag
     *
     * The helper uses the bitwise '&' operator against the hex values of the
     * render constants. This means that the flag can is "bitwised" value of
     * the render constants. Examples:
     * <code>
     * // render all links except glossary
     * $flag = Zend_View_Helper_Navigation_Links:RENDER_ALL ^
     *         Zend_View_Helper_Navigation_Links:RENDER_GLOSSARY;
     * $helper->setRenderFlag($flag);
     *
     * // render only chapters and sections
     * $flag = Zend_View_Helper_Navigation_Links:RENDER_CHAPTER |
     *         Zend_View_Helper_Navigation_Links:RENDER_SECTION;
     * $helper->setRenderFlag($flag);
     *
     * // render only relations that are not native W3C relations
     * $helper->setRenderFlag(Zend_View_Helper_Navigation_Links:RENDER_CUSTOM);
     *
     * // render all relations (default)
     * $helper->setRenderFlag(Zend_View_Helper_Navigation_Links:RENDER_ALL);
     * </code>
     *
     * Note that custom relations can also be rendered directly using the
     * {@link renderLink()} method.
     *
     * @param  int $renderFlag                    render flag
     * @return Zend_View_Helper_Navigation_Links  fluent interface, returns self
     */
    public function setRenderFlag($renderFlag)
    {
        $this->_renderFlag = (int) $renderFlag;
        return $this;
    }

    /**
     * Returns the helper's render flag
     *
     * @return int  render flag
     */
    public function getRenderFlag()
    {
        return $this->_renderFlag;
    }

    // Finder methods:

    /**
     * Finds all relations (forward and reverse) for the given $page
     *
     * The form of the returned array:
     * <code>
     * // $page denotes an instance of Zend_Navigation_Page
     * $returned = array(
     *     'rel' => array(
     *         'alternate' => array($page, $page, $page),
     *         'start'     => array($page),
     *         'next'      => array($page),
     *         'prev'      => array($page),
     *         'canonical' => array($page)
     *     ),
     *     'rev' => array(
     *         'section'   => array($page)
     *     )
     * );
     * </code>
     *
     * @param  Zend_Navigation_Page $page  page to find links for
     * @return array                       related pages
     */
    public function findAllRelations(Zend_Navigation_Page $page,
                                     $flag = null)
    {
        if (!is_int($flag)) {
            $flag = self::RENDER_ALL;
        }

        $result = array('rel' => array(), 'rev' => array());
        $native = array_values(self::$_RELATIONS);

        foreach (array_keys($result) as $rel) {
            $meth = 'getDefined' . ucfirst($rel);
            $types = array_merge($native, array_diff($page->$meth(), $native));

            foreach ($types as $type) {
                if (!$relFlag = array_search($type, self::$_RELATIONS)) {
                    $relFlag = self::RENDER_CUSTOM;
                }
                if (!($flag & $relFlag)) {
                    continue;
                }
                if ($found = $this->findRelation($page, $rel, $type)) {
                    if (!is_array($found)) {
                        $found = array($found);
                    }
                    $result[$rel][$type] = $found;
                }
            }
        }

        return $result;
    }

    /**
     * Finds relations of the given $rel=$type from $page
     *
     * This method will first look for relations in the page instance, then
     * by searching the root container if nothing was found in the page.
     *
     * @param  Zend_Navigation_Page $page       page to find relations for
     * @param  string              $rel         relation, "rel" or "rev"
     * @param  string              $type        link type, e.g. 'start', 'next'
     * @return Zend_Navigaiton_Page|array|null  page(s), or null if not found
     * @throws Zend_View_Exception              if $rel is not "rel" or "rev"
     */
    public function findRelation(Zend_Navigation_Page $page, $rel, $type)
    {
        if (!in_array($rel, array('rel', 'rev'))) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf(
                'Invalid argument: $rel must be "rel" or "rev"; "%s" given',
                $rel));
            $e->setView($this->view);
            throw $e;
        }

        if (!$result = $this->_findFromProperty($page, $rel, $type)) {
            $result = $this->_findFromSearch($page, $rel, $type);
        }

        return $result;
    }

    /**
     * Finds relations of given $type for $page by checking if the
     * relation is specified as a property of $page
     *
     * @param  Zend_Navigation_Page $page       page to find relations for
     * @param  string              $rel         relation, 'rel' or 'rev'
     * @param  string              $type        link type, e.g. 'start', 'next'
     * @return Zend_Navigation_Page|array|null  page(s), or null if not found
     */
    protected function _findFromProperty(Zend_Navigation_Page $page, $rel, $type)
    {
        $method = 'get' . ucfirst($rel);
        if ($result = $page->$method($type)) {
            if ($result = $this->_convertToPages($result)) {
                if (!is_array($result)) {
                    $result = array($result);
                }

                foreach ($result as $key => $page) {
                    if (!$this->accept($page)) {
                        unset($result[$key]);
                    }
                }

                return count($result) == 1 ? $result[0] : $result;
            }
        }

        return null;
    }

    /**
     * Finds relations of given $rel=$type for $page by using the helper to
     * search for the relation in the root container
     *
     * @param  Zend_Navigation_Page $page  page to find relations for
     * @param  string              $rel    relation, 'rel' or 'rev'
     * @param  string              $type   link type, e.g. 'start', 'next', etc
     * @return array|null                  array of pages, or null if not found
     */
    protected function _findFromSearch(Zend_Navigation_Page $page, $rel, $type)
    {
        $found = null;

        $method = 'search' . ucfirst($rel) . ucfirst($type);
        if (method_exists($this, $method)) {
            $found = $this->$method($page);
        }

        return $found;
    }

    // Search methods:

    /**
     * Searches the root container for the forward 'start' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the first document in a collection of documents. This link type
     * tells search engines which document is considered by the author to be the
     * starting point of the collection.
     *
     * @param  Zend_Navigation_Page $page  page to find relation for
     * @return Zend_Navigation_Page|null   page or null
     */
    public function searchRelStart(Zend_Navigation_Page $page)
    {
        $found = $this->_findRoot($page);
        if (!$found instanceof Zend_Navigation_Page) {
            $found->rewind();
            $found = $found->current();
        }

        if ($found === $page || !$this->accept($found)) {
            $found = null;
        }

        return $found;
    }

    /**
     * Searches the root container for the forward 'next' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the next document in a linear sequence of documents. User
     * agents may choose to preload the "next" document, to reduce the perceived
     * load time.
     *
     * @param  Zend_Navigation_Page $page  page to find relation for
     * @return Zend_Navigation_Page|null   page(s) or null
     */
    public function searchRelNext(Zend_Navigation_Page $page)
    {
        $found = null;
        $break = false;
        $iterator = new RecursiveIteratorIterator($this->_findRoot($page),
                RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $intermediate) {
            if ($intermediate === $page) {
                // current page; break at next accepted page
                $break = true;
                continue;
            }

            if ($break && $this->accept($intermediate)) {
                $found = $intermediate;
                break;
            }
        }

        return $found;
    }

    /**
     * Searches the root container for the forward 'prev' relation of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to the previous document in an ordered series of documents. Some
     * user agents also support the synonym "Previous".
     *
     * @param  Zend_Navigation_Page $page  page to find relation for
     * @return Zend_Navigation_Page|null   page or null
     */
    public function searchRelPrev(Zend_Navigation_Page $page)
    {
        $found = null;
        $prev = null;
        $iterator = new RecursiveIteratorIterator(
                $this->_findRoot($page),
                RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $intermediate) {
            if (!$this->accept($intermediate)) {
                continue;
            }
            if ($intermediate === $page) {
                $found = $prev;
                break;
            }

            $prev = $intermediate;
        }

        return $found;
    }

    /**
     * Searches the root container for forward 'chapter' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a chapter in a collection of documents.
     *
     * @param  Zend_Navigation_Page $page       page to find relation for
     * @return Zend_Navigation_Page|array|null  page(s) or null
     */
    public function searchRelChapter(Zend_Navigation_Page $page)
    {
        $found = array();

        // find first level of pages
        $root = $this->_findRoot($page);

        // find start page(s)
        $start = $this->findRelation($page, 'rel', 'start');
        if (!is_array($start)) {
            $start = array($start);
        }

        foreach ($root as $chapter) {
            // exclude self and start page from chapters
            if ($chapter !== $page &&
                !in_array($chapter, $start) &&
                $this->accept($chapter)) {
                $found[] = $chapter;
            }
        }

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for forward 'section' relations of the given
     * $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @param  Zend_Navigation_Page $page       page to find relation for
     * @return Zend_Navigation_Page|array|null  page(s) or null
     */
    public function searchRelSection(Zend_Navigation_Page $page)
    {
        $found = array();

        // check if given page has pages and is a chapter page
        if ($page->hasPages() && $this->_findRoot($page)->hasPage($page)) {
            foreach ($page as $section) {
                if ($this->accept($section)) {
                    $found[] = $section;
                }
            }
        }

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for forward 'subsection' relations of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @param  Zend_Navigation_Page $page       page to find relation for
     * @return Zend_Navigation_Page|array|null  page(s) or null
     */
    public function searchRelSubsection(Zend_Navigation_Page $page)
    {
        $found = array();

        if ($page->hasPages()) {
            // given page has child pages, loop chapters
            foreach ($this->_findRoot($page) as $chapter) {
                // is page a section?
                if ($chapter->hasPage($page)) {
                    foreach ($page as $subsection) {
                        if ($this->accept($subsection)) {
                            $found[] = $subsection;
                        }
                    }
                }
            }
        }

        switch (count($found)) {
            case 0:
                return null;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a section in a collection of documents.
     *
     * @param  Zend_Navigation_Page $page  page to find relation for
     * @return Zend_Navigation_Page|null   page(s) or null
     */
    public function searchRevSection(Zend_Navigation_Page $page)
    {
        $found = null;

        if ($parent = $page->getParent()) {
            if ($parent instanceof Zend_Navigation_Page &&
                $this->_findRoot($page)->hasPage($parent)) {
                $found = $parent;
            }
        }

        return $found;
    }

    /**
     * Searches the root container for the reverse 'section' relation of the
     * given $page
     *
     * From {@link http://www.w3.org/TR/html4/types.html#type-links}:
     * Refers to a document serving as a subsection in a collection of
     * documents.
     *
     * @param  Zend_Navigation_Page $page  page to find relation for
     * @return Zend_Navigation_Page|null   page(s) or null
     */
    public function searchRevSubsection(Zend_Navigation_Page $page)
    {
        $found = null;

        if ($parent = $page->getParent()) {
            if ($parent instanceof Zend_Navigation_Page) {
                $root = $this->_findRoot($page);
                foreach ($root as $chapter) {
                    if ($chapter->hasPage($parent)) {
                        $found = $parent;
                        break;
                    }
                }
            }
        }

        return $found;
    }

    // Util methods:

    /**
     * Returns the root container of the given page
     *
     * When rendering a container, the render method still store the given
     * container as the root container, and unset it when done rendering. This
     * makes sure finder methods will not traverse above the container given
     * to the render method.
     *
     * @param  Zend_Navigaiton_Page $page  page to find root for
     * @return Zend_Navigation_Container   the root container of the given page
     */
    protected function _findRoot(Zend_Navigation_Page $page)
    {
        if ($this->_root) {
            return $this->_root;
        }

        $root = $page;

        while ($parent = $page->getParent()) {
            $root = $parent;
            if ($parent instanceof Zend_Navigation_Page) {
                $page = $parent;
            } else {
                break;
            }
        }

        return $root;
    }

    /**
     * Converts a $mixed value to an array of pages
     *
     * @param  mixed $mixed                     mixed value to get page(s) from
     * @param  bool  $recursive                 whether $value should be looped
     *                                          if it is an array or a config
     * @return Zend_Navigation_Page|array|null  empty if unable to convert
     */
    protected function _convertToPages($mixed, $recursive = true)
    {
        if (is_object($mixed)) {
            if ($mixed instanceof Zend_Navigation_Page) {
                // value is a page instance; return directly
                return $mixed;
            } elseif ($mixed instanceof Zend_Navigation_Container) {
                // value is a container; return pages in it
                $pages = array();
                foreach ($mixed as $page) {
                    $pages[] = $page;
                }
                return $pages;
            } elseif ($mixed instanceof Zend_Config) {
                // convert config object to array and extract
                return $this->_convertToPages($mixed->toArray(), $recursive);
            }
        } elseif (is_string($mixed)) {
            // value is a string; make an URI page
            return Zend_Navigation_Page::factory(array(
                'type' => 'uri',
                'uri'  => $mixed
            ));
        } elseif (is_array($mixed) && !empty($mixed)) {
            if ($recursive && is_numeric(key($mixed))) {
                // first key is numeric; assume several pages
                $pages = array();
                foreach ($mixed as $value) {
                    if ($value = $this->_convertToPages($value, false)) {
                        $pages[] = $value;
                    }
                }
                return $pages;
            } else {
                // pass array to factory directly
                try {
                    $page = Zend_Navigation_Page::factory($mixed);
                    return $page;
                } catch (Exception $e) {
                }
            }
        }

        // nothing found
        return null;
    }

    // Render methods:

    /**
     * Renders the given $page as a link element, with $attrib = $relation
     *
     * @param  Zend_Navigation_Page $page      the page to render the link for
     * @param  string               $attrib    the attribute to use for $type,
     *                                         either 'rel' or 'rev'
     * @param  string               $relation  relation type, muse be one of;
     *                                         alternate, appendix, bookmark,
     *                                         chapter, contents, copyright,
     *                                         glossary, help, home, index, next,
     *                                         prev, section, start, stylesheet,
     *                                         subsection
     * @return string                          rendered link element
     * @throws Zend_View_Exception             if $attrib is invalid
     */
    public function renderLink(Zend_Navigation_Page $page, $attrib, $relation)
    {
        if (!in_array($attrib, array('rel', 'rev'))) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf(
                    'Invalid relation attribute "%s", must be "rel" or "rev"',
                    $attrib));
            $e->setView($this->view);
            throw $e;
        }

        if (!$href = $page->getHref()) {
            return '';
        }

        // TODO: add more attribs
        // http://www.w3.org/TR/html401/struct/links.html#h-12.2
        $attribs = array(
            $attrib  => $relation,
            'href'   => $href,
            'title'  => $page->getLabel()
        );

        return '<link' .
               $this->_htmlAttribs($attribs) .
               $this->getClosingBracket();
    }

    // Zend_View_Helper_Navigation_Helper:

    /**
     * Renders helper
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::render()}.
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function render(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        if ($active = $this->findActive($container)) {
            $active = $active['page'];
        } else {
            // no active page
            return '';
        }

        $output = '';
        $indent = $this->getIndent();
        $this->_root = $container;

        $result = $this->findAllRelations($active, $this->getRenderFlag());
        foreach ($result as $attrib => $types) {
            foreach ($types as $relation => $pages) {
                foreach ($pages as $page) {
                    if ($r = $this->renderLink($page, $attrib, $relation)) {
                        $output .= $indent . $r . self::EOL;
                    }
                }
            }
        }

        $this->_root = null;

        // return output (trim last newline by spec)
        return strlen($output) ? rtrim($output, self::EOL) : '';
    }
}
