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
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

#require_once "Zend/Tool/Framework/Client/Response/ContentDecorator/Interface.php";

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Console_ResponseDecorator_Indention
    implements Zend_Tool_Framework_Client_Response_ContentDecorator_Interface
{
    public function getName()
    {
        return 'indention';
    }

    /**
     * @param string $content
     * @param integer $indention
     */
    public function decorate($content, $indention)
    {
        if(strval(intval($indention)) != $indention) {
            return $content;
        }

        $newContent = "";
        $lines = preg_split('((\r\n|\r|\n)+)', $content);
        $lineIndention = str_repeat(' ', $indention);
        foreach($lines AS $line) {
            $newContent .= $lineIndention.$line.PHP_EOL;
        }
        return rtrim($newContent);
    }
}
