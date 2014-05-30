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
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SimplePost.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Represents a publicly available post
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Delicious_SimplePost
{
    /**
     * @var string Post url
     */
    protected $_url;

    /**
     * @var string Post title
     */
    protected $_title;

    /**
     * @var string Post notes
     */
    protected $_notes;

    /**
     * @var array Post tags
     */
    protected $_tags = array();

    /**
     * Constructor
     *
     * @param   array $post Post data
     * @return  void
     * @throws  Zend_Service_Delicious_Exception
     */
    public function __construct(array $post)
    {
        if (!isset($post['u']) || !isset($post['d'])) {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception('Title and URL not set.');
        }

        $this->_url   = $post['u'];
        $this->_title = $post['d'];

        if (isset($post['t'])) {
            $this->_tags = $post['t'];
        }
        if (isset($post['n'])) {
            $this->_notes = $post['n'];
        }
    }

    /**
     * Getter for URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Getter for title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Getter for notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->_notes;
    }

    /**
     * Getter for tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }
}
