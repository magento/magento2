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
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Table.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * PDF file reference table
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Reference_Table
{
    /**
     * Parent reference table
     *
     * @var Zend_Pdf_Element_Reference_Table
     */
    private $_parent;

    /**
     * Free entries
     * 'reference' => next free object number
     *
     * @var array
     */
    private $_free;

    /**
     * Generation numbers for free objects.
     * Array: objNum => nextGeneration
     *
     * @var array
     */
    private $_generations;

    /**
     * In use entries
     * 'reference' => offset
     *
     * @var array
     */
    private $_inuse;

    /**
     * Generation numbers for free objects.
     * Array: objNum => objGeneration
     *
     * @var array
     */
    private $_usedObjects;



    /**
     * Object constructor
     */
    public function  __construct()
    {
        $this->_parent = null;
        $this->_free   = array();  $this->_generations = array();
        $this->_inuse  = array();  $this->_usedObjects = array();
    }


    /**
     * Add reference to the reference table
     *
     * @param string $ref
     * @param integer $offset
     * @param boolean $inuse
     */
    public function addReference($ref, $offset, $inuse = true)
    {
        $refElements = explode(' ', $ref);
        if (!is_numeric($refElements[0]) || !is_numeric($refElements[1]) || $refElements[2] != 'R') {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Incorrect reference: '$ref'");
        }
        $objNum = (int)$refElements[0];
        $genNum = (int)$refElements[1];

        if ($inuse) {
            $this->_inuse[$ref]          = $offset;
            $this->_usedObjects[$objNum] = $objNum;
        } else {
            $this->_free[$ref]           = $offset;
            $this->_generations[$objNum] = $genNum;
        }
    }


    /**
     * Set parent reference table
     *
     * @param Zend_Pdf_Element_Reference_Table $parent
     */
    public function setParent(self $parent)
    {
        $this->_parent = $parent;
    }


    /**
     * Get object offset
     *
     * @param string $ref
     * @return integer
     */
    public function getOffset($ref)
    {
        if (isset($this->_inuse[$ref])) {
            return $this->_inuse[$ref];
        }

        if (isset($this->_free[$ref])) {
            return null;
        }

        if (isset($this->_parent)) {
            return $this->_parent->getOffset($ref);
        }

        return null;
    }


    /**
     * Get next object from a list of free objects.
     *
     * @param string $ref
     * @return integer
     * @throws Zend_Pdf_Exception
     */
    public function getNextFree($ref)
    {
        if (isset($this->_inuse[$ref])) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Object is not free');
        }

        if (isset($this->_free[$ref])) {
            return $this->_free[$ref];
        }

        if (isset($this->_parent)) {
            return $this->_parent->getNextFree($ref);
        }

        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Object not found.');
    }


    /**
     * Get next generation number for free object
     *
     * @param integer $objNum
     * @return unknown
     */
    public function getNewGeneration($objNum)
    {
        if (isset($this->_usedObjects[$objNum])) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Object is not free');
        }

        if (isset($this->_generations[$objNum])) {
            return $this->_generations[$objNum];
        }

        if (isset($this->_parent)) {
            return $this->_parent->getNewGeneration($objNum);
        }

        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Object not found.');
    }
}
