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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Loader_IncludePathLoader_RecursiveFilterIterator extends RecursiveFilterIterator
{

    protected $_denyDirectoryPattern = null;
    protected $_acceptFilePattern    = null;

    /**
     * constructor
     *
     * @param RecursiveIterator $iterator
     * @param string $denyDirectoryPattern
     * @param string $acceptFilePattern
     */
    public function __construct(RecursiveIterator $iterator, $denyDirectoryPattern = null, $acceptFilePattern = null)
    {
        $this->_denyDirectoryPattern = $denyDirectoryPattern;
        $this->_acceptFilePattern    = $acceptFilePattern;
        parent::__construct($iterator);
    }

    /**
     * accept() - Which iterable items to accept or deny, required by FilterInterface
     *
     * @return unknown
     */
    public function accept()
    {
        $currentNode = $this->current();
        $currentNodeRealPath = $currentNode->getRealPath();

        // if the current node is a directory AND doesn't match the denyDirectory pattern, accept
        if ($currentNode->isDir()
            && !preg_match('#' . $this->_denyDirectoryPattern . '#', $currentNodeRealPath)) {
            return true;
        }

        // if the file matches the accept file pattern, accept
        $acceptable = (preg_match('#' . $this->_acceptFilePattern . '#', $currentNodeRealPath)) ? true : false;
        return $acceptable;
    }

    /**
     * getChildren() - overridden from RecursiveFilterIterator to allow the persistence of
     * the $_denyDirectoryPattern and the $_acceptFilePattern when sub iterators of this filter
     * are needed to be created.
     *
     * @return Zend_Tool_Framework_Loader_IncludePathLoader_RecursiveFilterIterator
     */
    public function getChildren()
    {
        if (empty($this->ref)) {
            $this->ref = new ReflectionClass($this);
        }

        return $this->ref->newInstance(
            $this->getInnerIterator()->getChildren(),
            $this->_denyDirectoryPattern,
            $this->_acceptFilePattern
            );
    }

}

