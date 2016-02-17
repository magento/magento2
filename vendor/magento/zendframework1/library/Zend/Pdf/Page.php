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
#require_once 'Zend/Pdf/Resource/Unified.php';

#require_once 'Zend/Pdf/Canvas/Abstract.php';


/**
 * PDF Page
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Page extends Zend_Pdf_Canvas_Abstract
{
  /**** Class Constants ****/


  /* Page Sizes */

    /**
     * Size representing an A4 page in portrait (tall) orientation.
     */
    const SIZE_A4                = '595:842:';

    /**
     * Size representing an A4 page in landscape (wide) orientation.
     */
    const SIZE_A4_LANDSCAPE      = '842:595:';

    /**
     * Size representing a US Letter page in portrait (tall) orientation.
     */
    const SIZE_LETTER            = '612:792:';

    /**
     * Size representing a US Letter page in landscape (wide) orientation.
     */
    const SIZE_LETTER_LANDSCAPE  = '792:612:';


  /* Shape Drawing */

    /**
     * Stroke the path only. Do not fill.
     */
    const SHAPE_DRAW_STROKE      = 0;

    /**
     * Fill the path only. Do not stroke.
     */
    const SHAPE_DRAW_FILL        = 1;

    /**
     * Fill and stroke the path.
     */
    const SHAPE_DRAW_FILL_AND_STROKE = 2;


  /* Shape Filling Methods */

    /**
     * Fill the path using the non-zero winding rule.
     */
    const FILL_METHOD_NON_ZERO_WINDING = 0;

    /**
     * Fill the path using the even-odd rule.
     */
    const FILL_METHOD_EVEN_ODD        = 1;


  /* Line Dash Types */

    /**
     * Solid line dash.
     */
    const LINE_DASHING_SOLID = 0;



    /**
     * Page dictionary (refers to an inderect Zend_Pdf_Element_Dictionary object).
     *
     * @var Zend_Pdf_Element_Reference|Zend_Pdf_Element_Object
     */
    protected $_dictionary;

    /**
     * PDF objects factory.
     *
     * @var Zend_Pdf_ElementFactory_Interface
     */
    protected $_objFactory = null;

    /**
     * Flag which signals, that page is created separately from any PDF document or
     * attached to anyone.
     *
     * @var boolean
     */
    protected $_attached;

    /**
     * Safe Graphics State semafore
     *
     * If it's false, than we can't be sure Graphics State is restored withing
     * context of previous contents stream (ex. drawing coordinate system may be rotated).
     * We should encompass existing content with save/restore GS operators
     *
     * @var boolean
     */
    protected $_safeGS;

    /**
     * Object constructor.
     * Constructor signatures:
     *
     * 1. Load PDF page from a parsed PDF file.
     *    Object factory is created by PDF parser.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(Zend_Pdf_Element_Dictionary       $pageDict,
     *                   Zend_Pdf_ElementFactory_Interface $factory);
     * ---------------------------------------------------------
     *
     * 2. Make a copy of the PDF page.
     *    New page is created in the same context as source page. Object factory is shared.
     *    Thus it will be attached to the document, but need to be placed into Zend_Pdf::$pages array
     *    to be included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(Zend_Pdf_Page $page);
     * ---------------------------------------------------------
     *
     * 3. Create new page with a specified pagesize.
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(string $pagesize, Zend_Pdf_ElementFactory_Interface $factory = null);
     * ---------------------------------------------------------
     *
     * 4. Create new page with a specified pagesize (in default user space units).
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(numeric $width, numeric $height, Zend_Pdf_ElementFactory_Interface $factory = null);
     * ---------------------------------------------------------
     *
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @throws Zend_Pdf_Exception
     */
    public function __construct($param1, $param2 = null, $param3 = null)
    {
        if (($param1 instanceof Zend_Pdf_Element_Reference ||
             $param1 instanceof Zend_Pdf_Element_Object
            ) &&
            $param2 instanceof Zend_Pdf_ElementFactory_Interface &&
            $param3 === null
           ) {
            switch ($param1->getType()) {
                case Zend_Pdf_Element::TYPE_DICTIONARY:
                    $this->_dictionary = $param1;
                    $this->_objFactory = $param2;
                    $this->_attached   = true;
                    $this->_safeGS     = false;
                    return;
                    break;

                case Zend_Pdf_Element::TYPE_NULL:
                    $this->_objFactory = $param2;
                    $pageWidth = $pageHeight = 0;
                    break;

                default:
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Unrecognized object type.');
                    break;

            }
        } else if ($param1 instanceof Zend_Pdf_Page && $param2 === null && $param3 === null) {
            // Duplicate existing page.
            // Let already existing content and resources to be shared between pages
            // We don't give existing content modification functionality, so we don't need "deep copy"
            $this->_objFactory = $param1->_objFactory;
            $this->_attached   = &$param1->_attached;
            $this->_safeGS     = false;

            $this->_dictionary = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());

            foreach ($param1->_dictionary->getKeys() as $key) {
                if ($key == 'Contents') {
                    // Clone Contents property

                    $this->_dictionary->Contents = new Zend_Pdf_Element_Array();

                    if ($param1->_dictionary->Contents->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
                        // Prepare array of content streams and add existing stream
                        $this->_dictionary->Contents->items[] = $param1->_dictionary->Contents;
                    } else {
                        // Clone array of the content streams
                        foreach ($param1->_dictionary->Contents->items as $srcContentStream) {
                            $this->_dictionary->Contents->items[] = $srcContentStream;
                        }
                    }
                } else {
                    $this->_dictionary->$key = $param1->_dictionary->$key;
                }
            }

            return;
        } else if (is_string($param1) &&
                   ($param2 === null || $param2 instanceof Zend_Pdf_ElementFactory_Interface) &&
                   $param3 === null) {
            if ($param2 !== null) {
                $this->_objFactory = $param2;
            } else {
                #require_once 'Zend/Pdf/ElementFactory.php';
                $this->_objFactory = Zend_Pdf_ElementFactory::createFactory(1);
            }
            $this->_attached   = false;
            $this->_safeGS     = true; /** New page created. That's users App responsibility to track GS changes */

            switch (strtolower($param1)) {
                case 'a4':
                    $param1 = Zend_Pdf_Page::SIZE_A4;
                    break;
                case 'a4-landscape':
                    $param1 = Zend_Pdf_Page::SIZE_A4_LANDSCAPE;
                    break;
                case 'letter':
                    $param1 = Zend_Pdf_Page::SIZE_LETTER;
                    break;
                case 'letter-landscape':
                    $param1 = Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE;
                    break;
                default:
                    // should be in "x:y" or "x:y:" form
            }

            $pageDim = explode(':', $param1);
            if(count($pageDim) == 2  ||  count($pageDim) == 3) {
                $pageWidth  = $pageDim[0];
                $pageHeight = $pageDim[1];
            } else {
                /**
                 * @todo support of user defined pagesize notations, like:
                 *       "210x297mm", "595x842", "8.5x11in", "612x792"
                 */
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Wrong pagesize notation.');
            }
            /**
             * @todo support of pagesize recalculation to "default user space units"
             */

        } else if (is_numeric($param1) && is_numeric($param2) &&
                   ($param3 === null || $param3 instanceof Zend_Pdf_ElementFactory_Interface)) {
            if ($param3 !== null) {
                $this->_objFactory = $param3;
            } else {
                #require_once 'Zend/Pdf/ElementFactory.php';
                $this->_objFactory = Zend_Pdf_ElementFactory::createFactory(1);
            }

            $this->_attached = false;
            $this->_safeGS   = true; /** New page created. That's users App responsibility to track GS changes */
            $pageWidth  = $param1;
            $pageHeight = $param2;

        } else {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Unrecognized method signature, wrong number of arguments or wrong argument types.');
        }

        $this->_dictionary = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
        $this->_dictionary->Type         = new Zend_Pdf_Element_Name('Page');
        #require_once 'Zend/Pdf.php';
        $this->_dictionary->LastModified = new Zend_Pdf_Element_String(Zend_Pdf::pdfDate());
        $this->_dictionary->Resources    = new Zend_Pdf_Element_Dictionary();
        $this->_dictionary->MediaBox     = new Zend_Pdf_Element_Array();
        $this->_dictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric(0);
        $this->_dictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric(0);
        $this->_dictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric($pageWidth);
        $this->_dictionary->MediaBox->items[] = new Zend_Pdf_Element_Numeric($pageHeight);
        $this->_dictionary->Contents     = new Zend_Pdf_Element_Array();
    }


    /**
     * Attach resource to the canvas
     *
     * Method returns a name of the resource which can be used
     * as a resource reference within drawing instructions stream
     * Allowed types: 'ExtGState', 'ColorSpace', 'Pattern', 'Shading',
     * 'XObject', 'Font', 'Properties'
     *
     * @param string $type
     * @param Zend_Pdf_Resource $resource
     * @return string
     */
    protected function _attachResource($type, Zend_Pdf_Resource $resource)
    {
        // Check that Resources dictionary contains appropriate resource set
        if ($this->_dictionary->Resources->$type === null) {
            $this->_dictionary->Resources->touch();
            $this->_dictionary->Resources->$type = new Zend_Pdf_Element_Dictionary();
        } else {
            $this->_dictionary->Resources->$type->touch();
        }

        // Check, that resource is already attached to resource set.
        $resObject = $resource->getResource();
        foreach ($this->_dictionary->Resources->$type->getKeys() as $ResID) {
            if ($this->_dictionary->Resources->$type->$ResID === $resObject) {
                return $ResID;
            }
        }

        $idCounter = 1;
        do {
            $newResName = $type[0] . $idCounter++;
        } while ($this->_dictionary->Resources->$type->$newResName !== null);

        $this->_dictionary->Resources->$type->$newResName = $resObject;
        $this->_objFactory->attach($resource->getFactory());

        return $newResName;
    }

    /**
     * Add procedureSet to the Page description
     *
     * @param string $procSetName
     */
    protected function _addProcSet($procSetName)
    {
        // Check that Resources dictionary contains ProcSet entry
        if ($this->_dictionary->Resources->ProcSet === null) {
            $this->_dictionary->Resources->touch();
            $this->_dictionary->Resources->ProcSet = new Zend_Pdf_Element_Array();
        } else {
            $this->_dictionary->Resources->ProcSet->touch();
        }

        foreach ($this->_dictionary->Resources->ProcSet->items as $procSetEntry) {
            if ($procSetEntry->value == $procSetName) {
                // Procset is already included into a ProcSet array
                return;
            }
        }

        $this->_dictionary->Resources->ProcSet->items[] = new Zend_Pdf_Element_Name($procSetName);
    }

    /**
     * Returns dictionaries of used resources.
     *
     * Used for canvas implementations interoperability
     *
     * Structure of the returned array:
     * array(
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   ...
     *   'ProcSet' => array()
     * )
     *
     * where ProcSet array is a list of used procedure sets names (strings).
     * Allowed procedure set names: 'PDF', 'Text', 'ImageB', 'ImageC', 'ImageI'
     *
     * @internal
     * @return array
     */
    public function getResources()
    {
        $resources = array();
        $resDictionary = $this->_dictionary->Resources;

        foreach ($resDictionary->getKeys() as $resType) {
            $resources[$resType] = array();

            if ($resType == 'ProcSet') {
                foreach ($resDictionary->ProcSet->items as $procSetEntry) {
                    $resources[$resType][] = $procSetEntry->value;
                }
            } else {
                $resMap = $resDictionary->$resType;

                foreach ($resMap->getKeys() as $resId) {
                    $resources[$resType][$resId] =new Zend_Pdf_Resource_Unified($resMap->$resId);
                }
            }
        }

        return $resources;
    }

    /**
     * Get drawing instructions stream
     *
     * It has to be returned as a PDF stream object to make it reusable.
     *
     * @internal
     * @returns Zend_Pdf_Resource_ContentStream
     */
    public function getContents()
    {
        /** @todo implementation */
    }

    /**
     * Return the height of this page in points.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->_dictionary->MediaBox->items[3]->value -
               $this->_dictionary->MediaBox->items[1]->value;
    }

    /**
     * Return the width of this page in points.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->_dictionary->MediaBox->items[2]->value -
               $this->_dictionary->MediaBox->items[0]->value;
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     */
    public function __clone()
    {
        $factory = Zend_Pdf_ElementFactory::createFactory(1);
        $processed = array();

        // Clone dictionary object.
        // Do it explicitly to prevent sharing page attributes between different
        // results of clonePage() operation (other resources are still shared)
        $dictionary = new Zend_Pdf_Element_Dictionary();
        foreach ($this->_dictionary->getKeys() as $key) {
            $dictionary->$key = $this->_dictionary->$key->makeClone($factory->getFactory(),
                                                                        $processed,
                                                                        Zend_Pdf_Element::CLONE_MODE_SKIP_PAGES);
        }

        $this->_dictionary = $factory->newObject($dictionary);
        $this->_objFactory     = $factory;
        $this->_attached       = false;
        $this->_style          = null;
        $this->_font           = null;
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     *
     * @internal
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @param array $processed
     * @return Zend_Pdf_Page
     */
    public function clonePage($factory, &$processed)
    {
        // Clone dictionary object.
        // Do it explicitly to prevent sharing page attributes between different
        // results of clonePage() operation (other resources are still shared)
        $dictionary = new Zend_Pdf_Element_Dictionary();
        foreach ($this->_dictionary->getKeys() as $key) {
            $dictionary->$key = $this->_dictionary->$key->makeClone($factory->getFactory(),
                                                                        $processed,
                                                                        Zend_Pdf_Element::CLONE_MODE_SKIP_PAGES);
        }

        $clonedPage = new Zend_Pdf_Page($factory->newObject($dictionary), $factory);
        $clonedPage->_attached = false;

        return $clonedPage;
    }

    /**
     * Retrive PDF file reference to the page
     *
     * @internal
     * @return Zend_Pdf_Element_Dictionary
     */
    public function getPageDictionary()
    {
        return $this->_dictionary;
    }

    /**
     * Dump current drawing instructions into the content stream.
     *
     * @todo Don't forget to close all current graphics operations (like path drawing)
     *
     * @throws Zend_Pdf_Exception
     */
    public function flush()
    {
        if ($this->_saveCount != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Saved graphics state is not restored');
        }

        if ($this->_contents == '') {
            return;
        }

        if ($this->_dictionary->Contents->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
            /**
             * It's a stream object.
             * Prepare Contents page attribute for update.
             */
            $this->_dictionary->touch();

            $currentPageContents = $this->_dictionary->Contents;
            $this->_dictionary->Contents = new Zend_Pdf_Element_Array();
            $this->_dictionary->Contents->items[] = $currentPageContents;
        } else {
            $this->_dictionary->Contents->touch();
        }

        if ((!$this->_safeGS)  &&  (count($this->_dictionary->Contents->items) != 0)) {
            /**
             * Page already has some content which is not treated as safe.
             *
             * Add save/restore GS operators
             */
            $this->_addProcSet('PDF');

            $newContentsArray = new Zend_Pdf_Element_Array();
            $newContentsArray->items[] = $this->_objFactory->newStreamObject(" q\n");
            foreach ($this->_dictionary->Contents->items as $contentStream) {
                $newContentsArray->items[] = $contentStream;
            }
            $newContentsArray->items[] = $this->_objFactory->newStreamObject(" Q\n");

            $this->_dictionary->touch();
            $this->_dictionary->Contents = $newContentsArray;

            $this->_safeGS = true;
        }

        $this->_dictionary->Contents->items[] =
                $this->_objFactory->newStreamObject($this->_contents);

        $this->_contents = '';
    }

    /**
     * Prepare page to be rendered into PDF.
     *
     * @todo Don't forget to close all current graphics operations (like path drawing)
     *
     * @param Zend_Pdf_ElementFactory_Interface $objFactory
     * @throws Zend_Pdf_Exception
     */
    public function render(Zend_Pdf_ElementFactory_Interface $objFactory)
    {
        $this->flush();

        if ($objFactory === $this->_objFactory) {
            // Page is already attached to the document.
            return;
        }

        if ($this->_attached) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Page is attached to other documen. Use clone $page to get it context free.');
        } else {
            $objFactory->attach($this->_objFactory);
        }
    }

    /**
     * Extract resources attached to the page
     *
     * This method is not intended to be used in userland, but helps to optimize some document wide operations
     *
     * returns array of Zend_Pdf_Element_Dictionary objects
     *
     * @internal
     * @return array
     */
    public function extractResources()
    {
        return $this->_dictionary->Resources;
    }

    /**
     * Extract fonts attached to the page
     *
     * returns array of Zend_Pdf_Resource_Font_Extracted objects
     *
     * @return array
     * @throws Zend_Pdf_Exception
     */
    public function extractFonts()
    {
        if ($this->_dictionary->Resources->Font === null) {
            // Page doesn't have any font attached
            // Return empty array
            return array();
        }

        $fontResources = $this->_dictionary->Resources->Font;

        $fontResourcesUnique = array();
        foreach ($fontResources->getKeys() as $fontResourceName) {
            $fontDictionary = $fontResources->$fontResourceName;

            if (! ($fontDictionary instanceof Zend_Pdf_Element_Reference  ||
                   $fontDictionary instanceof Zend_Pdf_Element_Object) ) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Font dictionary has to be an indirect object or object reference.');
            }

            $fontResourcesUnique[spl_object_hash($fontDictionary->getObject())] = $fontDictionary;
        }

        $fonts = array();
        #require_once 'Zend/Pdf/Exception.php';
        foreach ($fontResourcesUnique as $resourceId => $fontDictionary) {
            try {
                #require_once 'Zend/Pdf/Resource/Font/Extracted.php';
                // Try to extract font
                $extractedFont = new Zend_Pdf_Resource_Font_Extracted($fontDictionary);

                $fonts[$resourceId] = $extractedFont;
            } catch (Zend_Pdf_Exception $e) {
                if ($e->getMessage() != 'Unsupported font type.') {
                    throw new Zend_Pdf_Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $fonts;
    }

    /**
     * Extract font attached to the page by specific font name
     *
     * $fontName should be specified in UTF-8 encoding
     *
     * @return Zend_Pdf_Resource_Font_Extracted|null
     * @throws Zend_Pdf_Exception
     */
    public function extractFont($fontName)
    {
        if ($this->_dictionary->Resources->Font === null) {
            // Page doesn't have any font attached
            return null;
        }

        $fontResources = $this->_dictionary->Resources->Font;

        $fontResourcesUnique = array();

        #require_once 'Zend/Pdf/Exception.php';
        foreach ($fontResources->getKeys() as $fontResourceName) {
            $fontDictionary = $fontResources->$fontResourceName;

            if (! ($fontDictionary instanceof Zend_Pdf_Element_Reference  ||
                   $fontDictionary instanceof Zend_Pdf_Element_Object) ) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Font dictionary has to be an indirect object or object reference.');
            }

            $resourceId = spl_object_hash($fontDictionary->getObject());
            if (isset($fontResourcesUnique[$resourceId])) {
                continue;
            } else {
                // Mark resource as processed
                $fontResourcesUnique[$resourceId] = 1;
            }

            if ($fontDictionary->BaseFont->value != $fontName) {
                continue;
            }

            try {
                // Try to extract font
                #require_once 'Zend/Pdf/Resource/Font/Extracted.php';
                return new Zend_Pdf_Resource_Font_Extracted($fontDictionary);
            } catch (Zend_Pdf_Exception $e) {
                if ($e->getMessage() != 'Unsupported font type.') {
                    throw new Zend_Pdf_Exception($e->getMessage(), $e->getCode(), $e);
                }

                // Continue searhing font with specified name
            }
        }

        return null;
    }

    /**
     *
     * @param Zend_Pdf_Annotation $annotation
     * @return Zend_Pdf_Page
     */
    public function attachAnnotation(Zend_Pdf_Annotation $annotation)
    {
        $annotationDictionary = $annotation->getResource();
        if (!$annotationDictionary instanceof Zend_Pdf_Element_Object  &&
            !$annotationDictionary instanceof Zend_Pdf_Element_Reference) {
            $annotationDictionary = $this->_objFactory->newObject($annotationDictionary);
        }

        if ($this->_dictionary->Annots === null) {
            $this->_dictionary->touch();
            $this->_dictionary->Annots = new Zend_Pdf_Element_Array();
        } else {
            $this->_dictionary->Annots->touch();
        }

        $this->_dictionary->Annots->items[] = $annotationDictionary;

        $annotationDictionary->touch();
        $annotationDictionary->P = $this->_dictionary;

        return $this;
    }
}

