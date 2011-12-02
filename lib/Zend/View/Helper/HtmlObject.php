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
 * @version    $Id: HtmlObject.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_View_Helper_HtmlElement
 */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlObject extends Zend_View_Helper_HtmlElement
{
    /**
     * Output an object set
     *
     * @param string $data The data file
     * @param string $type Data file type
     * @param array  $attribs Attribs for the object tag
     * @param array  $params Params for in the object tag
     * @param string $content Alternative content for object
     * @return string
     */
    public function htmlObject($data, $type, array $attribs = array(), array $params = array(), $content = null)
    {
        // Merge data and type
        $attribs = array_merge(array('data' => $data,
                                     'type' => $type), $attribs);

        // Params
        $paramHtml = array();
        $closingBracket = $this->getClosingBracket();

        foreach ($params as $param => $options) {
            if (is_string($options)) {
                $options = array('value' => $options);
            }

            $options = array_merge(array('name' => $param), $options);

            $paramHtml[] = '<param' . $this->_htmlAttribs($options) . $closingBracket;
        }

        // Content
        if (is_array($content)) {
            $content = implode(self::EOL, $content);
        }

        // Object header
        $xhtml = '<object' . $this->_htmlAttribs($attribs) . '>' . self::EOL
                 . implode(self::EOL, $paramHtml) . self::EOL
                 . ($content ? $content . self::EOL : '')
                 . '</object>';

        return $xhtml;
    }
}
