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
 * @version    $Id: ImageResult.php 20096 2010-01-06 02:05:09Z bkarwin $
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
class Zend_Service_Yahoo_ImageResult extends Zend_Service_Yahoo_Result
{
    /**
     * Summary info for the image
     *
     * @var string
     */
    public $Summary;

    /**
     * The URL of the webpage hosting the image
     *
     * @var string
     */
    public $RefererUrl;

    /**
     * The size of the files in bytes
     *
     * @var string
     */
    public $FileSize;

    /**
     * The type of file (bmp, gif, jpeg, etc.)
     *
     * @var string
     */
    public $FileFormat;

    /**
     * The height of the image in pixels
     *
     * @var string
     */
    public $Height;

    /**
     * The width of the image in pixels
     *
     * @var string
     */
    public $Width;

    /**
     * The thubmnail image for the article, if it exists
     *
     * @var Zend_Service_Yahoo_Image
     */
    public $Thumbnail;

    /**
     * Image result namespace
     *
     * @var string
     */
    protected $_namespace = 'urn:yahoo:srchmi';


    /**
     * Initializes the image result
     *
     * @param  DOMElement $result
     * @return void
     */
    public function __construct(DOMElement $result)
    {
        $this->_fields = array('Summary', 'RefererUrl', 'FileSize', 'FileFormat', 'Height', 'Width', 'Thumbnail');

        parent::__construct($result);

        $this->_setThumbnail();
    }
}
