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
 * Helper for printing breadcrumbs
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Navigation_Breadcrumbs
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**
     * Breadcrumbs separator string
     *
     * @var string
     */
    protected $_separator = ' &gt; ';

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $_minDepth = 1;

    /**
     * Whether last page in breadcrumb should be hyperlinked
     *
     * @var bool
     */
    protected $_linkLast = false;

    /**
     * Partial view script to use for rendering menu
     *
     * @var string|array
     */
    protected $_partial;

    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container     [optional] container to
     *                                                  operate on
     * @return Zend_View_Helper_Navigation_Breadcrumbs  fluent interface,
     *                                                  returns self
     */
    public function breadcrumbs(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets breadcrumb separator
     *
     * @param  string $separator                        separator string
     * @return Zend_View_Helper_Navigation_Breadcrumbs  fluent interface,
     *                                                  returns self
     */
    public function setSeparator($separator)
    {
        if (is_string($separator)) {
            $this->_separator = $separator;
        }

        return $this;
    }

    /**
     * Returns breadcrumb separator
     *
     * @return string  breadcrumb separator
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked
     *
     * @param  bool $linkLast                           whether last page should
     *                                                  be hyperlinked
     * @return Zend_View_Helper_Navigation_Breadcrumbs  fluent interface,
     *                                                  returns self
     */
    public function setLinkLast($linkLast)
    {
        $this->_linkLast = (bool) $linkLast;
        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked
     *
     * @return bool  whether last page in breadcrumbs should be hyperlinked
     */
    public function getLinkLast()
    {
        return $this->_linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu
     *
     * @param  string|array $partial                    partial view script or
     *                                                  null. If an array is
     *                                                  given, it is expected to
     *                                                  contain two values;
     *                                                  the partial view script
     *                                                  to use, and the module
     *                                                  where the script can be
     *                                                  found.
     * @return Zend_View_Helper_Navigation_Breadcrumbs  fluent interface,
     *                                                  returns self
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

    // Render methods:

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function renderStraight(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->htmlify($active);
        } else {
            $html = $active->getLabel();
            if ($this->getUseTranslator() && $t = $this->getTranslator()) {
                $html = $t->translate($html);
            }
            $html = $this->view->escape($html);
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof Zend_Navigation_Page) {
                // prepend crumb to html
                $html = $this->htmlify($parent)
                      . $this->getSeparator()
                      . $html;
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return strlen($html) ? $this->getIndent() . $html : '';
    }

    /**
     * Renders the given $container by invoking the partial view helper
     *
     * The container will simply be passed on as a model to the view script,
     * so in the script it will be available in <code>$this->container</code>.
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               pass to view script.
     *                                               Default is to use the
     *                                               container registered in the
     *                                               helper.
     * @param  string|array             $partial     [optional] partial view
     *                                               script to use. Default is
     *                                               to use the partial
     *                                               registered in the helper.
     *                                               If an array is given, it is
     *                                               expected to contain two
     *                                               values; the partial view
     *                                               script to use, and the
     *                                               module where the script can
     *                                               be found.
     * @return string                                helper output
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

        // put breadcrumb pages in model
        $model = array('pages' => array());
        if ($active = $this->findActive($container)) {
            $active = $active['page'];
            $model['pages'][] = $active;
            while ($parent = $active->getParent()) {
                if ($parent instanceof Zend_Navigation_Page) {
                    $model['pages'][] = $parent;
                } else {
                    break;
                }

                if ($parent === $container) {
                    // break if at the root of the given container
                    break;
                }

                $active = $parent;
            }
            $model['pages'] = array_reverse($model['pages']);
        }

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
        if ($partial = $this->getPartial()) {
            return $this->renderPartial($container, $partial);
        } else {
            return $this->renderStraight($container);
        }
    }
}
