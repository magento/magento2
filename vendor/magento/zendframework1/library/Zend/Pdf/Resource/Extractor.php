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
 * @version    $Id:
 */

/** Internally used classes */
#require_once 'Zend/Pdf/Element.php';
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/String/Binary.php';
#require_once 'Zend/Pdf/Element/Boolean.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Null.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/**
 * Resource extractor class is used to detach resources from original PDF document.
 *
 * It provides resources sharing, so different pages or other PDF resources can share
 * its dependent resources (e.g. fonts or images) or other resources still use them without duplication.
 * It also reduces output PDF size, required memory for PDF processing and
 * processing time.
 *
 * The same extractor may be used for different source documents, several
 * extractors may be used for constracting one target document, but extractor
 * must not be shared between target documents.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Extractor
{
    /**
     * PDF objects factory.
     *
     * @var Zend_Pdf_ElementFactory_Interface
     */
    protected $_factory;

    /**
     * Reusable list of already processed objects
     *
     * @var array
     */
    protected $_processed;

    /**
     * Object constructor.
     */
    public function __construct()
    {
        $this->_factory   = Zend_Pdf_ElementFactory::createFactory(1);
        $this->_processed = array();
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs
     *
     * return Zend_Pdf_Page
     */
    public function clonePage(Zend_Pdf_Page $page)
    {
        return $page->clonePage($this->_factory, $this->_processed);
    }
}

