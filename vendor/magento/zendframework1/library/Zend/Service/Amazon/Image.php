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
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Image
{
    /**
     * Image URL
     *
     * @var Zend_Uri
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
     * Assigns values to properties relevant to Image
     *
     * @param  DOMElement $dom
     * @return void
     */
    public function __construct(DOMElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $this->Url = Zend_Uri::factory($xpath->query('./az:URL/text()', $dom)->item(0)->data);
        $this->Height = (int) $xpath->query('./az:Height/text()', $dom)->item(0)->data;
        $this->Width = (int) $xpath->query('./az:Width/text()', $dom)->item(0)->data;
    }
}
