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
 * @package    Zend_Markup
 * @subpackage Renderer_Html
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Code.php 20270 2010-01-13 22:37:41Z kokx $
 */

/**
 * @see Zend_Markup_Renderer_Html_HtmlAbstract
 */
#require_once 'Zend/Markup/Renderer/Html/HtmlAbstract.php';

/**
 * Tag interface
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Renderer_Html
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Renderer_Html_Code extends Zend_Markup_Renderer_Html_HtmlAbstract
{

    /**
     * Convert the token
     *
     * @param Zend_Markup_Token $token
     * @param string $text
     *
     * @return string
     */
    public function convert(Zend_Markup_Token $token, $text)
    {
        return highlight_string($text, true);
    }

}
