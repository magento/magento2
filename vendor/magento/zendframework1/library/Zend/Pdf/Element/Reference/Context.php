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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * PDF reference object context
 * Reference context is defined by PDF parser and PDF Refernce table
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Reference_Context
{
    /**
     * PDF parser object.
     *
     * @var Zend_Pdf_StringParser
     */
    private $_stringParser;

    /**
     * Reference table
     *
     * @var Zend_Pdf_Element_Reference_Table
     */
    private $_refTable;

    /**
     * Object constructor
     *
     * @param Zend_Pdf_StringParser $parser
     * @param Zend_Pdf_Element_Reference_Table $refTable
     */
    public function __construct(Zend_Pdf_StringParser $parser,
                                Zend_Pdf_Element_Reference_Table $refTable)
    {
        $this->_stringParser = $parser;
        $this->_refTable     = $refTable;
    }


    /**
     * Context parser
     *
     * @return Zend_Pdf_StringParser
     */
    public function getParser()
    {
        return $this->_stringParser;
    }


    /**
     * Context reference table
     *
     * @return Zend_Pdf_Element_Reference_Table
     */
    public function getRefTable()
    {
        return $this->_refTable;
    }
}

