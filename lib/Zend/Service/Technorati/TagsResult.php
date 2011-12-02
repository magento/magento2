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
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TagsResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Result
 */
#require_once 'Zend/Service/Technorati/Result.php';


/**
 * Represents a single Technorati TopTags or BlogPostTags query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid Zend_Service_Technorati_TagsResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_TagsResult extends Zend_Service_Technorati_Result
{
    /**
     * Name of the tag.
     *
     * @var     string
     * @access  protected
     */
    protected $_tag;

    /**
     * Number of posts containing this tag.
     *
     * @var     int
     * @access  protected
     */
    protected $_posts;


    /**
     * Constructs a new object object from DOM Document.
     *
     * @param   DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->_fields = array( '_tag'   => 'tag',
                                '_posts' => 'posts');
        parent::__construct($dom);

        // filter fields
        $this->_tag   = (string) $this->_tag;
        $this->_posts = (int) $this->_posts;
    }

    /**
     * Returns the tag name.
     *
     * @return  string
     */
    public function getTag() {
        return $this->_tag;
    }

    /**
     * Returns the number of posts.
     *
     * @return  int
     */
    public function getPosts() {
        return $this->_posts;
    }
}
