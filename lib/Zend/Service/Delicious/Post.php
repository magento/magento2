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
 * @version    $Id: Post.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Date
 */
#require_once 'Zend/Date.php';

/**
 * @see Zend_Service_Delicious_SimplePost
 */
#require_once 'Zend/Service/Delicious/SimplePost.php';


/**
 * Zend_Service_Delicious_Post represents a post of a user that can be edited
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Delicious_Post extends Zend_Service_Delicious_SimplePost
{
    /**
     * Service that has downloaded the post
     *
     * @var Zend_Service_Delicious
     */
    protected $_service;

    /**
     * @var int Number of people that have the same post
     */
    protected $_others;

    /**
     * @var Zend_Date Post date
     */
    protected $_date;

    /**
     * @var bool Post share
     */
    protected $_shared = true;

    /**
     * @var string Post hash
     */
    protected $_hash;

    /**
     * Constructs a new del.icio.us post
     *
     * @param  Zend_Service_Delicious $service Service that has downloaded the post
     * @param  DOMElement|array       $values  Post content
     * @throws Zend_Service_Delicious_Exception
     * @return void
     */
    public function __construct(Zend_Service_Delicious $service, $values)
    {
        $this->_service = $service;

        if ($values instanceof DOMElement) {
            $values = self::_parsePostNode($values);
        }

        if (!is_array($values) || !isset($values['url']) || !isset($values['title'])) {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception("Second argument must be array with at least 2 keys ('url' and"
                                                     . " 'title')");
        }

        if (isset($values['date']) && ! $values['date'] instanceof Zend_Date) {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception("Date has to be an instance of Zend_Date");
        }

        foreach (array('url', 'title', 'notes', 'others', 'tags', 'date', 'shared', 'hash') as $key) {
            if (isset($values[$key])) {
                $this->{"_$key"}  = $values[$key];
            }
        }
    }

    /**
     * Setter for title
     *
     * @param  string $newTitle
     * @return Zend_Service_Delicious_Post
     */
    public function setTitle($newTitle)
    {
        $this->_title = (string) $newTitle;

        return $this;
    }

    /**
     * Setter for notes
     *
     * @param  string $newNotes
     * @return Zend_Service_Delicious_Post
     */
    public function setNotes($newNotes)
    {
        $this->_notes = (string) $newNotes;

        return $this;
    }

    /**
     * Setter for tags
     *
     * @param  array $tags
     * @return Zend_Service_Delicious_Post
     */
    public function setTags(array $tags)
    {
        $this->_tags = $tags;

        return $this;
    }

    /**
     * Add a tag
     *
     * @param  string $tag
     * @return Zend_Service_Delicious_Post
     */
    public function addTag($tag)
    {
        $this->_tags[] = (string) $tag;

        return $this;
    }

    /**
     * Remove a tag
     *
     * @param  string $tag
     * @return Zend_Service_Delicious_Post
     */
    public function removeTag($tag)
    {
        $this->_tags = array_diff($this->_tags, array((string) $tag));

        return $this;
    }

    /**
     * Getter for date
     *
     * @return Zend_Date
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Getter for others
     *
     * This property is only populated when posts are retrieved
     * with getPosts() method. The getAllPosts() and getRecentPosts()
     * methods will not populate this property.
     *
     * @return int
     */
    public function getOthers()
    {
        return $this->_others;
    }

    /**
     * Getter for hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Getter for shared
     *
     * @return bool
     */
    public function getShared()
    {
        return $this->_shared;
    }

    /**
     * Setter for shared
     *
     * @param  bool $isShared
     * @return Zend_Service_Delicious_Post
     */
    public function setShared($isShared)
    {
        $this->_shared = (bool) $isShared;

        return $this;
    }

    /**
     * Deletes post
     *
     * @return Zend_Service_Delicious
     */
    public function delete()
    {
        return $this->_service->deletePost($this->_url);
    }

    /**
     * Saves post
     *
     * @return DOMDocument
     */
    public function save()
    {
        $parms = array(
            'url'        => $this->_url,
            'description'=> $this->_title,
            'extended'   => $this->_notes,
            'shared'     => ($this->_shared ? 'yes' : 'no'),
            'tags'       => implode(' ', (array) $this->_tags),
            'replace'    => 'yes'
        );
        /*
        if ($this->_date instanceof Zend_Date) {
            $parms['dt'] = $this->_date->get('Y-m-d\TH:i:s\Z');
        }
        */

        return $this->_service->makeRequest(Zend_Service_Delicious::PATH_POSTS_ADD, $parms);
    }

    /**
     * Extracts content from the DOM element of a post
     *
     * @param  DOMElement $node
     * @return array
     */
    protected static function _parsePostNode(DOMElement $node)
    {
        return array(
            'url'    => $node->getAttribute('href'),
            'title'  => $node->getAttribute('description'),
            'notes'  => $node->getAttribute('extended'),
            'others' => (int) $node->getAttribute('others'),
            'tags'   => explode(' ', $node->getAttribute('tag')),
            /**
             * @todo replace strtotime() with Zend_Date equivalent
             */
            'date'   => new Zend_Date(strtotime($node->getAttribute('time'))),
            'shared' => ($node->getAttribute('shared') == 'no' ? false : true),
            'hash'   => $node->getAttribute('hash')
        );
    }
}
