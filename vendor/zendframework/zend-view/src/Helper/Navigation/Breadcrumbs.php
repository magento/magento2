<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;
use Zend\View;
use Zend\View\Exception;

/**
 * Helper for printing breadcrumbs
 */
class Breadcrumbs extends AbstractHelper
{
    /**
     * Whether last page in breadcrumb should be hyperlinked
     *
     * @var bool
     */
    protected $linkLast = false;

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $minDepth = 1;

    /**
     * Partial view script to use for rendering menu
     *
     * @var string|array
     */
    protected $partial;

    /**
     * Breadcrumbs separator string
     *
     * @var string
     */
    protected $separator = ' &gt; ';

    /**
     * Helper entry point
     *
     * @param  string|AbstractContainer $container container to operate on
     * @return Breadcrumbs
     */
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                                      to render the container registered in the helper.
     * @return string
     */
    public function render($container = null)
    {
        $partial = $this->getPartial();
        if ($partial) {
            return $this->renderPartial($container, $partial);
        }

        return $this->renderStraight($container);
    }

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                                      to render the container registered in the helper.
     * @return string
     */
    public function renderStraight($container = null)
    {
        $this->parseContainer($container);
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
            /** @var \Zend\View\Helper\EscapeHtml $escaper */
            $escaper = $this->view->plugin('escapeHtml');
            $html    = $escaper(
                $this->translate($active->getLabel(), $active->getTextDomain())
            );
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof AbstractPage) {
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
     * @param  AbstractContainer $container [optional] container to pass to view script.
     *                              Default is to use the container registered
     *                              in the helper.
     * @param  string|array $partial [optional] partial view script to use.
     *                               Default is to use the partial registered
     *                               in the helper.  If an array is given, it
     *                               is expected to contain two values; the
     *                               partial view script to use, and the module
     *                               where the script can be found.
     * @throws Exception\RuntimeException if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     * @return string               helper output
     */
    public function renderPartial($container = null, $partial = null)
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            throw new Exception\RuntimeException(
                'Unable to render menu: No partial view script provided'
            );
        }

        // put breadcrumb pages in model
        $model = array(
            'pages' => array(),
            'separator' => $this->getSeparator()
        );
        $active = $this->findActive($container);
        if ($active) {
            $active = $active['page'];
            $model['pages'][] = $active;
            while ($parent = $active->getParent()) {
                if ($parent instanceof AbstractPage) {
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

        /** @var \Zend\View\Helper\Partial $partialHelper */
        $partialHelper = $this->view->plugin('partial');

        if (is_array($partial)) {
            if (count($partial) != 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render menu: A view partial supplied as '
                    .  'an array must contain two values: partial view '
                    .  'script and module where script can be found'
                );
            }

            return $partialHelper($partial[0], $model);
        }

        return $partialHelper($partial, $model);
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked
     *
     * @param  bool $linkLast whether last page should be hyperlinked
     * @return Breadcrumbs
     */
    public function setLinkLast($linkLast)
    {
        $this->linkLast = (bool) $linkLast;
        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked
     *
     * @return bool
     */
    public function getLinkLast()
    {
        return $this->linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu
     *
     * @param  string|array $partial partial view script or null. If an array is
     *                               given, it is expected to contain two
     *                               values; the partial view script to use,
     *                               and the module where the script can be
     *                               found.
     * @return Breadcrumbs
     */
    public function setPartial($partial)
    {
        if (null === $partial || is_string($partial) || is_array($partial)) {
            $this->partial = $partial;
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
        return $this->partial;
    }

    /**
     * Sets breadcrumb separator
     *
     * @param  string $separator separator string
     * @return Breadcrumbs
     */
    public function setSeparator($separator)
    {
        if (is_string($separator)) {
            $this->separator = $separator;
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
        return $this->separator;
    }
}
