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
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FSMAction.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Abstract Finite State Machine
 *
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_FSMAction
{
    /**
     * Object reference
     *
     * @var object
     */
    private $_object;

    /**
     * Method name
     *
     * @var string
     */
    private $_method;

    /**
     * Object constructor
     *
     * @param object $object
     * @param string $method
     */
    public function __construct($object, $method)
    {
        $this->_object = $object;
        $this->_method = $method;
    }

    public function doAction()
    {
        $methodName = $this->_method;
        $this->_object->$methodName();
    }
}

