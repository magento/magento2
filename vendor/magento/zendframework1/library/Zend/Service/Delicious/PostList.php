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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * List of posts retrived from the del.icio.us web service
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Delicious_PostList implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array Array of Zend_Service_Delicious_Post
     */
    protected $_posts = array();

    /**
     * @var Zend_Service_Delicious Service that has downloaded the post list
     */
    protected $_service;

    /**
     * @var int Iterator key
     */
    protected $_iteratorKey = 0;

    /**
     * @param  Zend_Service_Delicious $service Service that has downloaded the post
     * @param  DOMNodeList|array      $posts
     * @return void
     */
    public function __construct(Zend_Service_Delicious $service, $posts = null)
    {
        $this->_service = $service;
        if ($posts instanceof DOMNodeList) {
            $this->_constructFromNodeList($posts);
        } else if (is_array($posts)) {
            $this->_constructFromArray($posts);
        }
    }

    /**
     * Transforms DOMNodeList to array of posts
     *
     * @param  DOMNodeList $nodeList
     * @return void
     */
    private function _constructFromNodeList(DOMNodeList $nodeList)
    {
        for ($i = 0; $i < $nodeList->length; $i++) {
            $curentNode = $nodeList->item($i);
            if($curentNode->nodeName == 'post') {
                $this->_addPost(new Zend_Service_Delicious_Post($this->_service, $curentNode));
            }
        }
    }

    /**
     * Transforms the Array to array of posts
     *
     * @param  array $postList
     * @return void
     */
    private function _constructFromArray(array $postList)
    {
        foreach ($postList as $f_post) {
            $this->_addPost(new Zend_Service_Delicious_SimplePost($f_post));
        }
    }

    /**
     * Add a post
     *
     * @param  Zend_Service_Delicious_SimplePost $post
     * @return Zend_Service_Delicious_PostList
     */
    protected function _addPost(Zend_Service_Delicious_SimplePost $post)
    {
        $this->_posts[] = $post;

        return $this;
    }

    /**
     * Filter list by list of tags
     *
     * @param  array $tags
     * @return Zend_Service_Delicious_PostList
     */
    public function withTags(array $tags)
    {
        $postList = new self($this->_service);

        foreach ($this->_posts as $post) {
            if (count(array_diff($tags, $post->getTags())) == 0) {
                $postList->_addPost($post);
            }
        }

        return $postList;
    }

    /**
     * Filter list by tag
     *
     * @param  string $tag
     * @return Zend_Service_Delicious_PostList
     */
    public function withTag($tag)
    {
        return $this->withTags(func_get_args());
    }

    /**
     * Filter list by urls matching a regular expression
     *
     * @param  string $regexp
     * @return Zend_Service_Delicious_PostList
     */
    public function withUrl($regexp)
    {
        $postList = new self($this->_service);

        foreach ($this->_posts as $post) {
            if (preg_match($regexp, $post->getUrl())) {
                $postList->_addPost($post);
            }
        }

        return $postList;
    }

    /**
     * Return number of posts
     *
     * Implement Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->_posts);
    }

    /**
     * Return the current element
     *
     * Implement Iterator::current()
     *
     * @return Zend_Service_Delicious_SimplePost
     */
    public function current()
    {
        return $this->_posts[$this->_iteratorKey];
    }

    /**
     * Return the key of the current element
     *
     * Implement Iterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->_iteratorKey;
    }

    /**
     * Move forward to next element
     *
     * Implement Iterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_iteratorKey += 1;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * Implement Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_iteratorKey = 0;
    }

    /**
     * Check if there is a current element after calls to rewind() or next()
     *
     * Implement Iterator::valid()
     *
     * @return bool
     */
    public function valid()
    {
        $numItems = $this->count();

        if ($numItems > 0 && $this->_iteratorKey < $numItems) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether the offset exists
     *
     * Implement ArrayAccess::offsetExists()
     *
     * @param   int     $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        return ($offset < $this->count());
    }

    /**
     * Return value at given offset
     *
     * Implement ArrayAccess::offsetGet()
     *
     * @param   int     $offset
     * @throws  OutOfBoundsException
     * @return  Zend_Service_Delicious_SimplePost
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->_posts[$offset];
        } else {
            throw new OutOfBoundsException('Illegal index');
        }
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetSet()
     *
     * @param   int     $offset
     * @param   string  $value
     * @throws  Zend_Service_Delicious_Exception
     */
    public function offsetSet($offset, $value)
    {
        /**
         * @see Zend_Service_Delicious_Exception
         */
        #require_once 'Zend/Service/Delicious/Exception.php';
        throw new Zend_Service_Delicious_Exception('You are trying to set read-only property');
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetUnset()
     *
     * @param   int     $offset
     * @throws  Zend_Service_Delicious_Exception
     */
    public function offsetUnset($offset)
    {
        /**
         * @see Zend_Service_Delicious_Exception
         */
        #require_once 'Zend/Service/Delicious/Exception.php';
        throw new Zend_Service_Delicious_Exception('You are trying to unset read-only property');
    }
}
