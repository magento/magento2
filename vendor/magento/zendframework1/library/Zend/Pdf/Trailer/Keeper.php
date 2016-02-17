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


/** Zend_Pdf_Trailer */
#require_once 'Zend/Pdf/Trailer.php';

/**
 * PDF file trailer.
 * Stores and provides access to the trailer parced from a PDF file
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Trailer_Keeper extends Zend_Pdf_Trailer
{
    /**
     * Reference context
     *
     * @var Zend_Pdf_Element_Reference_Context
     */
    private $_context;

    /**
     * Previous trailer
     *
     * @var Zend_Pdf_Trailer
     */
    private $_prev;


    /**
     * Object constructor
     *
     * @param Zend_Pdf_Element_Dictionary $dict
     * @param Zend_Pdf_Element_Reference_Context $context
     * @param Zend_Pdf_Trailer $prev
     */
    public function __construct(Zend_Pdf_Element_Dictionary $dict,
                                Zend_Pdf_Element_Reference_Context $context,
                                Zend_Pdf_Trailer $prev = null)
    {
        parent::__construct($dict);

        $this->_context = $context;
        $this->_prev    = $prev;
    }

    /**
     * Setter for $this->_prev
     *
     * @param Zend_Pdf_Trailer_Keeper $prev
     */
    public function setPrev(Zend_Pdf_Trailer_Keeper $prev)
    {
        $this->_prev = $prev;
    }

    /**
     * Getter for $this->_prev
     *
     * @return Zend_Pdf_Trailer
     */
    public function getPrev()
    {
        return $this->_prev;
    }

    /**
     * Get length of source PDF
     *
     * @return string
     */
    public function getPDFLength()
    {
        return $this->_context->getParser()->getLength();
    }

    /**
     * Get PDF String
     *
     * @return string
     */
    public function getPDFString()
    {
        return $this->_context->getParser()->getString();
    }

    /**
     * Get reference table, which corresponds to the trailer.
     * Proxy to the $_context member methad call
     *
     * @return Zend_Pdf_Element_Reference_Context
     */
    public function getRefTable()
    {
        return $this->_context->getRefTable();
    }

    /**
     * Get header of free objects list
     * Returns object number of last free object
     *
     * @throws Zend_Pdf_Exception
     * @return integer
     */
    public function getLastFreeObject()
    {
        try {
            $this->_context->getRefTable()->getNextFree('0 65535 R');
        } catch (Zend_Pdf_Exception $e) {
            if ($e->getMessage() == 'Object not found.') {
                /**
                 * Here is work around for some wrong generated PDFs.
                 * We have not found reference to the header of free object list,
                 * thus we treat it as there are no free objects.
                 */
                return 0;
            }

            throw new Zend_Pdf_Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
