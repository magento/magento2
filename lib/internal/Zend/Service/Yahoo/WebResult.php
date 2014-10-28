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
 * @version    $Id: WebResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Yahoo_Result
 */
#require_once 'Zend/Service/Yahoo/Result.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_WebResult extends Zend_Service_Yahoo_Result
{
    /**
     * A summary of the result
     *
     * @var string
     */
    public $Summary;

    /**
     * The file type of the result (text, html, pdf, etc.)
     *
     * @var string
     */
    public $MimeType;

    /**
     * The modification time of the result (as a unix timestamp)
     *
     * @var string
     */
    public $ModificationDate;

    /**
     * The URL for the Yahoo cache of this page, if it exists
     *
     * @var string
     */
    public $CacheUrl;

    /**
     * The size of the cache entry
     *
     * @var int
     */
    public $CacheSize;

    /**
     * Web result namespace
     *
     * @var string
     */
    protected $_namespace = 'urn:yahoo:srch';


    /**
     * Initializes the web result
     *
     * @param  DOMElement $result
     * @return void
     */
    public function __construct(DOMElement $result)
    {
        $this->_fields = array('Summary', 'MimeType', 'ModificationDate');
        parent::__construct($result);

        $this->_xpath = new DOMXPath($result->ownerDocument);
        $this->_xpath->registerNamespace('yh', $this->_namespace);

        // check if the cache section exists
        $cacheUrl = $this->_xpath->query('./yh:Cache/yh:Url/text()', $result)->item(0);
        if ($cacheUrl instanceof DOMNode)
        {
            $this->CacheUrl = $cacheUrl->data;
        }
        $cacheSize = $this->_xpath->query('./yh:Cache/yh:Size/text()', $result)->item(0);
        if ($cacheSize instanceof DOMNode)
        {
            $this->CacheSize = (int) $cacheSize->data;
        }
    }
}
