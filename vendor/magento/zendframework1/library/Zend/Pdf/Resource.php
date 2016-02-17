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
 * PDF file Resource abstraction
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Resource
{
    /**
     * Each Pdf resource (fonts, images, ...) interacts with a PDF itself.
     * It creates appropriate PDF objects, structures and sometime embedded files.
     * Resources are referenced in content streams by names, which are stored in
     * a page resource dictionaries.
     *
     * Thus, resources must be attached to the PDF.
     *
     * Resource abstraction uses own PDF object factory to store all necessary information.
     * At the render time internal object factory is appended to the global PDF file
     * factory.
     *
     * Resource abstraction also cashes information about rendered PDF files and
     * doesn't duplicate resource description each time then Resource is rendered
     * (referenced).
     *
     * @var Zend_Pdf_ElementFactory_Interface
     */
    protected $_objectFactory;

    /**
     * Main resource object
     *
     * @var Zend_Pdf_Element_Object
     */
    protected $_resource;

    /**
     * Object constructor.
     *
     * If resource is not a Zend_Pdf_Element object, then stream object with specified value is
     * generated.
     *
     * @param Zend_Pdf_Element|string $resource
     */
    public function __construct($resource)
    {
        if ($resource instanceof Zend_Pdf_Element_Object) {
            $this->_objectFactory = $resource->getFactory();
            $this->_resource      = $resource;

            return;
        }

        #require_once 'Zend/Pdf/ElementFactory.php';

        $this->_objectFactory = Zend_Pdf_ElementFactory::createFactory(1);
        if ($resource instanceof Zend_Pdf_Element) {
            $this->_resource  = $this->_objectFactory->newObject($resource);
        } else {
            $this->_resource  = $this->_objectFactory->newStreamObject($resource);
        }
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     */
    public function __clone()
    {
        /** @todo implementation*/

//        $factory = Zend_Pdf_ElementFactory::createFactory(1);
//        $processed = array();
//
//        // Clone dictionary object.
//        // Do it explicitly to prevent sharing resource attributes between different
//        // results of clone operation (other resources are still shared)
//        $dictionary = new Zend_Pdf_Element_Dictionary();
//        foreach ($this->_pageDictionary->getKeys() as $key) {
//         $dictionary->$key = $this->_pageDictionary->$key->makeClone($factory->getFactory(),
//                                                                     $processed,
//                                                                     Zend_Pdf_Element::CLONE_MODE_SKIP_PAGES);
//        }
//
//        $this->_pageDictionary = $factory->newObject($dictionary);
//        $this->_objectFactory  = $factory;
//        $this->_attached       = false;
//        $this->_style          = null;
//        $this->_font           = null;
    }

    /**
     * Clone resource, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     *
     * @internal
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @param array $processed
     * @return Zend_Pdf_Page
     */
    public function cloneResource($factory, &$processed)
    {
        /** @todo implementation*/

//        // Clone dictionary object.
//        // Do it explicitly to prevent sharing page attributes between different
//        // results of clonePage() operation (other resources are still shared)
//        $dictionary = new Zend_Pdf_Element_Dictionary();
//        foreach ($this->_pageDictionary->getKeys() as $key) {
//            $dictionary->$key = $this->_pageDictionary->$key->makeClone($factory->getFactory(),
//                                                                        $processed,
//                                                                        Zend_Pdf_Element::CLONE_MODE_SKIP_PAGES);
//        }
//
//        $clonedPage = new Zend_Pdf_Page($factory->newObject($dictionary), $factory);
//        $clonedPage->_attached = false;
//
//        return $clonedPage;
    }

    /**
     * Get resource.
     * Used to reference resource in an internal PDF data structures (resource dictionaries)
     *
     * @internal
     * @return Zend_Pdf_Element_Object
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Get factory.
     *
     * @internal
     * @return Zend_Pdf_ElementFactory_Interface
     */
    public function getFactory()
    {
        return $this->_objectFactory;
    }
}
