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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CustomerReview.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_CustomerReview
{
    /**
     * @var string
     */
    public $Rating;

    /**
     * @var string
     */
    public $HelpfulVotes;

    /**
     * @var string
     */
    public $CustomerId;

    /**
     * @var string
     */
    public $TotalVotes;

    /**
     * @var string
     */
    public $Date;

    /**
     * @var string
     */
    public $Summary;

    /**
     * @var string
     */
    public $Content;

    /**
     * Assigns values to properties relevant to CustomerReview
     *
     * @param  DOMElement $dom
     * @return void
     */
    public function __construct(DOMElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2005-10-05');
        foreach (array('Rating', 'HelpfulVotes', 'CustomerId', 'TotalVotes', 'Date', 'Summary', 'Content') as $el) {
            $result = $xpath->query("./az:$el/text()", $dom);
            if ($result->length == 1) {
                $this->$el = (string) $result->item(0)->data;
            }
        }
    }
}
