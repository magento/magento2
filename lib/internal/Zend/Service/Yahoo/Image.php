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
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Image.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_Image
{
    /**
     * Image URL
     *
     * @var string
     */
    public $Url;

    /**
     * Image height in pixels
     *
     * @var int
     */
    public $Height;

    /**
     * Image width in pixels
     *
     * @var int
     */
    public $Width;


    /**
     * Initializes the image
     *
     * @param  DOMNode $dom
     * @param  string  $namespace
     * @return void
     */
    public function __construct(DOMNode $dom, $namespace)
    {
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('yh', $namespace);
        $this->Url = Zend_Uri::factory($xpath->query('./yh:Url/text()', $dom)->item(0)->data);
        $this->Height = (int) $xpath->query('./yh:Height/text()', $dom)->item(0)->data;
        $this->Width = (int) $xpath->query('./yh:Width/text()', $dom)->item(0)->data;
    }
}
