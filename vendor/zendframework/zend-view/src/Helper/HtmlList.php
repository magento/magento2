<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Exception;

/**
 * Helper for ordered and unordered lists
 */
class HtmlList extends AbstractHtmlElement
{
    /**
     * Generates a 'List' element.
     *
     * @param  array $items   Array with the elements of the list
     * @param  bool  $ordered Specifies ordered/unordered list; default unordered
     * @param  array $attribs Attributes for the ol/ul tag.
     * @param  bool  $escape  Escape the items.
     * @throws Exception\InvalidArgumentException
     * @return string The list XHTML.
     */
    public function __invoke(array $items, $ordered = false, $attribs = false, $escape = true)
    {
        if (empty($items)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$items array can not be empty in %s',
                __METHOD__
            ));
        }

        $list = '';

        foreach ($items as $item) {
            if (!is_array($item)) {
                if ($escape) {
                    $escaper = $this->getView()->plugin('escapeHtml');
                    $item    = $escaper($item);
                }
                $list .= '<li>' . $item . '</li>' . PHP_EOL;
            } else {
                $itemLength = 5 + strlen(PHP_EOL);
                if ($itemLength < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - $itemLength)
                     . $this($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                } else {
                    $list .= '<li>' . $this($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                }
            }
        }

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        $tag = ($ordered) ? 'ol' : 'ul';

        return '<' . $tag . $attribs . '>' . PHP_EOL . $list . '</' . $tag . '>' . PHP_EOL;
    }
}
