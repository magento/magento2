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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Markup_Renderer_Html
 */
#require_once 'Zend/Markup/Renderer/Html.php';
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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Renderer_Html_Url extends Zend_Markup_Renderer_Html_HtmlAbstract
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
        if ($token->hasAttribute('url')) {
            $uri = $token->getAttribute('url');
        } else {
            $uri = $text;
        }

        if (!preg_match('/^([a-z][a-z+\-.]*):/i', $uri)) {
            $uri = 'http://' . $uri;
        }

        // check if the URL is valid
        if (!Zend_Markup_Renderer_Html::isValidUri($uri)) {
            return $text;
        }

        $attributes = Zend_Markup_Renderer_Html::renderAttributes($token);

        // run the URI through htmlentities
        $uri = htmlentities($uri, ENT_QUOTES, Zend_Markup_Renderer_Html::getEncoding());

        return "<a href=\"{$uri}\"{$attributes}>{$text}</a>";
    }

}
