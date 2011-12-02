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
 * @version    $Id: HtmlFlash.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_View_Helper_HtmlObject
 */
#require_once 'Zend/View/Helper/HtmlObject.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlFlash extends Zend_View_Helper_HtmlObject
{
    /**
     * Default file type for a flash applet
     *
     */
    const TYPE = 'application/x-shockwave-flash';

    /**
     * Output a flash movie object tag
     *
     * @param string $data The flash file
     * @param array  $attribs Attribs for the object tag
     * @param array  $params Params for in the object tag
     * @param string $content Alternative content
     * @return string
     */
    public function htmlFlash($data, array $attribs = array(), array $params = array(), $content = null)
    {
        // Params
        $params = array_merge(array('movie'   => $data,
                                    'quality' => 'high'), $params);

        return $this->htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
