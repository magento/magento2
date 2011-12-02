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
 * @version    $Id: Result.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yahoo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yahoo_Result
{
    /**
     * The title of the search entry
     *
     * @var string
     */
    public $Title;

    /**
     * The URL of the found object
     *
     * @var string
     */
    public $Url;

    /**
     * The URL for linking to the found object
     *
     * @var string
     */
    public $ClickUrl;

    /**
     * Result fields
     *
     * @var array
     */
    protected $_fields;

    /**
     * REST response fragment for the result
     *
     * @var DOMElement
     */
    protected $_result;

    /**
     * Object for XPath queries
     *
     * @var DOMXPath
     */
    protected $_xpath;


    /**
     * Initializes the result
     *
     * @param  DOMElement $result
     * @return void
     */
    public function __construct(DOMElement $result)
    {
        // default fields for all search results:
        $fields = array('Title', 'Url', 'ClickUrl');

        // merge w/ child's fields
        $this->_fields = array_merge($this->_fields, $fields);

        $this->_xpath = new DOMXPath($result->ownerDocument);
        $this->_xpath->registerNamespace('yh', $this->_namespace);

        // add search results to appropriate fields

        foreach ($this->_fields as $f) {
            $query = "./yh:$f/text()";
            $node = $this->_xpath->query($query, $result);
            if ($node->length == 1) {
                $this->{$f} = $node->item(0)->data;
            }
        }

        $this->_result = $result;
    }


    /**
     * Sets the Thumbnail property
     *
     * @return void
     */
    protected function _setThumbnail()
    {
        $node = $this->_xpath->query('./yh:Thumbnail', $this->_result);
        if ($node->length == 1) {
            /**
             * @see Zend_Service_Yahoo_Image
             */
            #require_once 'Zend/Service/Yahoo/Image.php';
            $this->Thumbnail = new Zend_Service_Yahoo_Image($node->item(0), $this->_namespace);
        } else {
            $this->Thumbnail = null;
        }
    }
}
