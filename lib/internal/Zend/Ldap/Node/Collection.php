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
 * @package    Zend_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Collection.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Ldap_Collection
 */
#require_once 'Zend/Ldap/Collection.php';


/**
 * Zend_Ldap_Node_Collection provides a collecion of nodes.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_Collection extends Zend_Ldap_Collection
{
    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return Zend_Ldap_Node
     */
    protected function _createEntry(array $data)
    {
        /**
         * @see Zend_Ldap_Node
         */
        #require_once 'Zend/Ldap/Node.php';
        $node = Zend_Ldap_Node::fromArray($data, true);
        $node->attachLdap($this->_iterator->getLdap());
        return $node;
    }

    /**
     * Return the child key (DN).
     * Implements Iterator and RecursiveIterator
     *
     * @return string
     */
    public function key()
    {
        return $this->_iterator->key();
    }
}