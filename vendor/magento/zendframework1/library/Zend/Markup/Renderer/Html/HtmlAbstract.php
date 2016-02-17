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
 * @see Zend_Markup_Renderer_TokenConverterInterface
 */
#require_once 'Zend/Markup/Renderer/TokenConverterInterface.php';

/**
 * Tag interface
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Renderer_Html
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Markup_Renderer_Html_HtmlAbstract implements Zend_Markup_Renderer_TokenConverterInterface
{

    /**
     * The HTML renderer
     *
     * @var Zend_Markup_Renderer_Html
     */
    protected $_renderer;


    /**
     * Set the HTML renderer instance
     *
     * @param Zend_Markup_Renderer_Html $renderer
     *
     * @return Zend_Markup_Renderer_Html_HtmlAbstract
     */
    public function setRenderer(Zend_Markup_Renderer_Html $renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Get the HTML renderer instance
     *
     * @return Zend_Markup_Renderer_Html
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }
}
