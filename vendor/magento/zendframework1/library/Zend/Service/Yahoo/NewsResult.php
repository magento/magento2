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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Service_Yahoo_Result
 */
#require_once 'Zend/Service/Yahoo/Result.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_NewsResult extends Zend_Service_Yahoo_Result
{
    /**
     * Sumamry text associated with the result article
     *
     * @var string
     */
    public $Summary;

    /**
     * The company who distributed the article
     *
     * @var string
     */
    public $NewsSource;

    /**
     * The URL for the company who distributed the article
     *
     * @var string
     */
    public $NewsSourceUrl;

    /**
     * The language the article is in
     *
     * @var string
     */
    public $Language;

    /**
     * The date the article was published (in unix timestamp format)
     *
     * @var string
     */
    public $PublishDate;

    /**
     * The date the article was modified (in unix timestamp format)
     *
     * @var string
     */
    public $ModificationDate;

    /**
     * The thubmnail image for the article, if it exists
     *
     * @var Zend_Service_Yahoo_Image
     */
    public $Thumbnail;

    /**
     * News result namespace
     *
     * @var string
     */
    protected $_namespace = 'urn:yahoo:yn';


    /**
     * Initializes the news result
     *
     * @param  DOMElement $result
     * @return void
     */
    public function __construct(DOMElement $result)
    {
        $this->_fields = array('Summary', 'NewsSource', 'NewsSourceUrl', 'Language', 'PublishDate',
                               'ModificationDate', 'Thumbnail');

        parent::__construct($result);

        $this->_setThumbnail();
    }
}
