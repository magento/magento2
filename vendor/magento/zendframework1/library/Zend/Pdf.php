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


/** User land classes and interfaces turned on by Zend/Pdf.php file inclusion. */
/** @todo Section should be removed with ZF 2.0 release as obsolete            */

/** Zend_Pdf_Page */
#require_once 'Zend/Pdf/Page.php';

/** Zend_Pdf_Style */
#require_once 'Zend/Pdf/Style.php';

/** Zend_Pdf_Color_GrayScale */
#require_once 'Zend/Pdf/Color/GrayScale.php';

/** Zend_Pdf_Color_Rgb */
#require_once 'Zend/Pdf/Color/Rgb.php';

/** Zend_Pdf_Color_Cmyk */
#require_once 'Zend/Pdf/Color/Cmyk.php';

/** Zend_Pdf_Color_Html */
#require_once 'Zend/Pdf/Color/Html.php';

/** Zend_Pdf_Image */
#require_once 'Zend/Pdf/Image.php';

/** Zend_Pdf_Font */
#require_once 'Zend/Pdf/Font.php';

/** Zend_Pdf_Resource_Extractor */
#require_once 'Zend/Pdf/Resource/Extractor.php';

/** Zend_Pdf_Canvas */
#require_once 'Zend/Pdf/Canvas.php';


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
 * General entity which describes PDF document.
 * It implements document abstraction with a document level operations.
 *
 * Class is used to create new PDF document or load existing document.
 * See details in a class constructor description
 *
 * Class agregates document level properties and entities (pages, bookmarks,
 * document level actions, attachments, form object, etc)
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf
{
  /**** Class Constants ****/

    /**
     * Version number of generated PDF documents.
     */
    const PDF_VERSION = '1.4';

    /**
     * PDF file header.
     */
    const PDF_HEADER  = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";

    /**
     * Form field options
     */
    const PDF_FORM_FIELD_READONLY = 1;
    const PDF_FORM_FIELD_REQUIRED = 2;
    const PDF_FORM_FIELD_NOEXPORT = 4;

    /**
     * Pages collection
     *
     * @todo implement it as a class, which supports ArrayAccess and Iterator interfaces,
     *       to provide incremental parsing and pages tree updating.
     *       That will give good performance and memory (PDF size) benefits.
     *
     * @var array   - array of Zend_Pdf_Page object
     */
    public $pages = array();

    /**
     * Document properties
     *
     * It's an associative array with PDF meta information, values may
     * be string, boolean or float.
     * Returned array could be used directly to access, add, modify or remove
     * document properties.
     *
     * Standard document properties: Title (must be set for PDF/X documents), Author,
     * Subject, Keywords (comma separated list), Creator (the name of the application,
     * that created document, if it was converted from other format), Trapped (must be
     * true, false or null, can not be null for PDF/X documents)
     *
     * @var array
     */
    public $properties = array();

    /**
     * Original properties set.
     *
     * Used for tracking properties changes
     *
     * @var array
     */
    protected $_originalProperties = array();

    /**
     * Document level javascript
     *
     * @var string
     */
    protected $_javaScript = null;

    /**
     * Document named destinations or "GoTo..." actions, used to refer
     * document parts from outside PDF
     *
     * @var array   - array of Zend_Pdf_Target objects
     */
    protected $_namedTargets = array();

    /**
     * Document outlines
     *
     * @var array - array of Zend_Pdf_Outline objects
     */
    public $outlines = array();

    /**
     * Original document outlines list
     * Used to track outlines update
     *
     * @var array - array of Zend_Pdf_Outline objects
     */
    protected $_originalOutlines = array();

    /**
     * Original document outlines open elements count
     * Used to track outlines update
     *
     * @var integer
     */
    protected $_originalOpenOutlinesCount = 0;

    /**
     * Pdf trailer (last or just created)
     *
     * @var Zend_Pdf_Trailer
     */
    protected $_trailer = null;

    /**
     * PDF objects factory.
     *
     * @var Zend_Pdf_ElementFactory_Interface
     */
    protected $_objFactory = null;

    /**
     * Memory manager for stream objects
     *
     * @var Zend_Memory_Manager|null
     */
    protected static $_memoryManager = null;

    /**
     * Pdf file parser.
     * It's not used, but has to be destroyed only with Zend_Pdf object
     *
     * @var Zend_Pdf_Parser
     */
    protected $_parser;

    /**
     * List of inheritable attributesfor pages tree
     *
     * @var array
     */
    protected static $_inheritableAttributes = array('Resources', 'MediaBox', 'CropBox', 'Rotate');

    /**
     * List of form fields
     *
     * @var array - Associative array, key: name of form field, value: Zend_Pdf_Element
     */
    protected $_formFields = array();

    /**
     * True if the object is a newly created PDF document (affects save() method behavior)
     * False otherwise
     *
     * @var boolean
     */
    protected $_isNewDocument = true;

    /**
     * Request used memory manager
     *
     * @return Zend_Memory_Manager
     */
    static public function getMemoryManager()
    {
        if (self::$_memoryManager === null) {
            #require_once 'Zend/Memory.php';
            self::$_memoryManager = Zend_Memory::factory('none');
        }

        return self::$_memoryManager;
    }

    /**
     * Set user defined memory manager
     *
     * @param Zend_Memory_Manager $memoryManager
     */
    static public function setMemoryManager(Zend_Memory_Manager $memoryManager)
    {
        self::$_memoryManager = $memoryManager;
    }


    /**
     * Create new PDF document from a $source string
     *
     * @param string $source
     * @param integer $revision
     * @return Zend_Pdf
     */
    public static function parse(&$source = null, $revision = null)
    {
        return new Zend_Pdf($source, $revision);
    }

    /**
     * Load PDF document from a file
     *
     * @param string $source
     * @param integer $revision
     * @return Zend_Pdf
     */
    public static function load($source = null, $revision = null)
    {
        return new Zend_Pdf($source, $revision, true);
    }

    /**
     * Render PDF document and save it.
     *
     * If $updateOnly is true and it's not a new document, then it only
     * appends new section to the end of file.
     *
     * @param string $filename
     * @param boolean $updateOnly
     * @throws Zend_Pdf_Exception
     */
    public function save($filename, $updateOnly = false)
    {
        if (($file = @fopen($filename, $updateOnly ? 'ab':'wb')) === false ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Can not open '$filename' file for writing." );
        }

        $this->render($updateOnly, $file);

        fclose($file);
    }

    /**
     * Creates or loads PDF document.
     *
     * If $source is null, then it creates a new document.
     *
     * If $source is a string and $load is false, then it loads document
     * from a binary string.
     *
     * If $source is a string and $load is true, then it loads document
     * from a file.
     * $revision used to roll back document to specified version
     * (0 - current version, 1 - previous version, 2 - ...)
     *
     * @param string  $source - PDF file to load
     * @param integer $revision
     * @param bool    $load
     * @throws Zend_Pdf_Exception
     * @return Zend_Pdf
     */
    public function __construct($source = null, $revision = null, $load = false)
    {
        #require_once 'Zend/Pdf/ElementFactory.php';
        $this->_objFactory = Zend_Pdf_ElementFactory::createFactory(1);

        if ($source !== null) {
            #require_once 'Zend/Pdf/Parser.php';
            $this->_parser           = new Zend_Pdf_Parser($source, $this->_objFactory, $load);
            $this->_pdfHeaderVersion = $this->_parser->getPDFVersion();
            $this->_trailer          = $this->_parser->getTrailer();
            if ($this->_trailer->Encrypt !== null) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Encrypted document modification is not supported');
            }
            if ($revision !== null) {
                $this->rollback($revision);
            } else {
                $this->_loadPages($this->_trailer->Root->Pages);
            }

            $this->_loadNamedDestinations($this->_trailer->Root, $this->_parser->getPDFVersion());
            $this->_loadOutlines($this->_trailer->Root);
            $this->_loadJavaScript($this->_trailer->Root);
            $this->_loadFormFields($this->_trailer->Root);

            if ($this->_trailer->Info !== null) {
                $this->properties = $this->_trailer->Info->toPhp();

                if (isset($this->properties['Trapped'])) {
                    switch ($this->properties['Trapped']) {
                        case 'True':
                            $this->properties['Trapped'] = true;
                            break;

                        case 'False':
                            $this->properties['Trapped'] = false;
                            break;

                        case 'Unknown':
                            $this->properties['Trapped'] = null;
                            break;

                        default:
                            // Wrong property value
                            // Do nothing
                            break;
                    }
                }

                $this->_originalProperties = $this->properties;
            }

            $this->_isNewDocument = false;
        } else {
            $this->_pdfHeaderVersion = Zend_Pdf::PDF_VERSION;

            $trailerDictionary = new Zend_Pdf_Element_Dictionary();

            /**
             * Document id
             */
            $docId = md5(uniqid(rand(), true));   // 32 byte (128 bit) identifier
            $docIdLow  = substr($docId,  0, 16);  // first 16 bytes
            $docIdHigh = substr($docId, 16, 16);  // second 16 bytes

            $trailerDictionary->ID = new Zend_Pdf_Element_Array();
            $trailerDictionary->ID->items[] = new Zend_Pdf_Element_String_Binary($docIdLow);
            $trailerDictionary->ID->items[] = new Zend_Pdf_Element_String_Binary($docIdHigh);

            $trailerDictionary->Size = new Zend_Pdf_Element_Numeric(0);

            #require_once 'Zend/Pdf/Trailer/Generator.php';
            $this->_trailer = new Zend_Pdf_Trailer_Generator($trailerDictionary);

            /**
             * Document catalog indirect object.
             */
            $docCatalog = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
            $docCatalog->Type    = new Zend_Pdf_Element_Name('Catalog');
            $docCatalog->Version = new Zend_Pdf_Element_Name(Zend_Pdf::PDF_VERSION);
            $this->_trailer->Root = $docCatalog;

            /**
             * Pages container
             */
            $docPages = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
            $docPages->Type  = new Zend_Pdf_Element_Name('Pages');
            $docPages->Kids  = new Zend_Pdf_Element_Array();
            $docPages->Count = new Zend_Pdf_Element_Numeric(0);
            $docCatalog->Pages = $docPages;
        }
    }

    /**
     * Retrive number of revisions.
     *
     * @return integer
     */
    public function revisions()
    {
        $revisions = 1;
        $currentTrailer = $this->_trailer;

        while ($currentTrailer->getPrev() !== null && $currentTrailer->getPrev()->Root !== null ) {
            $revisions++;
            $currentTrailer = $currentTrailer->getPrev();
        }

        return $revisions++;
    }

    /**
     * Rollback document $steps number of revisions.
     * This method must be invoked before any changes, applied to the document.
     * Otherwise behavior is undefined.
     *
     * @param integer $steps
     */
    public function rollback($steps)
    {
        for ($count = 0; $count < $steps; $count++) {
            if ($this->_trailer->getPrev() !== null && $this->_trailer->getPrev()->Root !== null) {
                $this->_trailer = $this->_trailer->getPrev();
            } else {
                break;
            }
        }
        $this->_objFactory->setObjectCount($this->_trailer->Size->value);

        // Mark content as modified to force new trailer generation at render time
        $this->_trailer->Root->touch();

        $this->pages = array();
        $this->_loadPages($this->_trailer->Root->Pages);
    }

    /**
     * Load pages recursively
     *
     * @param Zend_Pdf_Element_Reference $pages
     * @param array|null                 $attributes
     * @throws Zend_Pdf_Exception
     */
    protected function _loadPages(Zend_Pdf_Element_Reference $pages, $attributes = array())
    {
        if ($pages->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Wrong argument');
        }

        foreach ($pages->getKeys() as $property) {
            if (in_array($property, self::$_inheritableAttributes)) {
                $attributes[$property] = $pages->$property;
                $pages->$property = null;
            }
        }


        foreach ($pages->Kids->items as $child) {
            if ($child->Type->value == 'Pages') {
                $this->_loadPages($child, $attributes);
            } else if ($child->Type->value == 'Page') {
                foreach (self::$_inheritableAttributes as $property) {
                    if ($child->$property === null && array_key_exists($property, $attributes)) {
                        /**
                         * Important note.
                         * If any attribute or dependant object is an indirect object, then it's still
                         * shared between pages.
                         */
                        if ($attributes[$property] instanceof Zend_Pdf_Element_Object  ||
                            $attributes[$property] instanceof Zend_Pdf_Element_Reference) {
                            $child->$property = $attributes[$property];
                        } else {
                            $child->$property = $this->_objFactory->newObject($attributes[$property]);
                        }
                    }
                }

                #require_once 'Zend/Pdf/Page.php';
                $this->pages[] = new Zend_Pdf_Page($child, $this->_objFactory);
            }
        }
    }

    /**
     * Load named destinations recursively
     *
     * @param Zend_Pdf_Element_Reference $root Document catalog entry
     * @param string $pdfHeaderVersion
     * @throws Zend_Pdf_Exception
     */
    protected function _loadNamedDestinations(Zend_Pdf_Element_Reference $root, $pdfHeaderVersion)
    {
        if ($root->Version !== null  &&  version_compare($root->Version->value, $pdfHeaderVersion, '>')) {
            $versionIs_1_2_plus = version_compare($root->Version->value,    '1.1', '>');
        } else {
            $versionIs_1_2_plus = version_compare($pdfHeaderVersion, '1.1', '>');
        }

        if ($versionIs_1_2_plus) {
            // PDF version is 1.2+
            // Look for Destinations structure at Name dictionary
            if ($root->Names !== null  &&  $root->Names->Dests !== null) {
                #require_once 'Zend/Pdf/NameTree.php';
                #require_once 'Zend/Pdf/Target.php';
                foreach (new Zend_Pdf_NameTree($root->Names->Dests) as $name => $destination) {
                    $this->_namedTargets[$name] = Zend_Pdf_Target::load($destination);
                }
            }
        } else {
            // PDF version is 1.1 (or earlier)
            // Look for Destinations sructure at Dest entry of document catalog
            if ($root->Dests !== null) {
                if ($root->Dests->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Document catalog Dests entry must be a dictionary.');
                }

                #require_once 'Zend/Pdf/Target.php';
                foreach ($root->Dests->getKeys() as $destKey) {
                    $this->_namedTargets[$destKey] = Zend_Pdf_Target::load($root->Dests->$destKey);
                }
            }
        }
    }

    /**
     * Load outlines recursively
     *
     * @param Zend_Pdf_Element_Reference $root Document catalog entry
     * @throws Zend_Pdf_Exception
     */
    protected function _loadOutlines(Zend_Pdf_Element_Reference $root)
    {
        if ($root->Outlines === null) {
            return;
        }

        if ($root->Outlines->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Document catalog Outlines entry must be a dictionary.');
        }

        if ($root->Outlines->Type !== null  &&  $root->Outlines->Type->value != 'Outlines') {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Outlines Type entry must be an \'Outlines\' string.');
        }

        if ($root->Outlines->First === null) {
            return;
        }

        $outlineDictionary = $root->Outlines->First;
        $processedDictionaries = new SplObjectStorage();
        while ($outlineDictionary !== null  &&  !$processedDictionaries->contains($outlineDictionary)) {
            $processedDictionaries->attach($outlineDictionary);

            #require_once 'Zend/Pdf/Outline/Loaded.php';
            $this->outlines[] = new Zend_Pdf_Outline_Loaded($outlineDictionary);

            $outlineDictionary = $outlineDictionary->Next;
        }

        $this->_originalOutlines = $this->outlines;

        if ($root->Outlines->Count !== null) {
            $this->_originalOpenOutlinesCount = $root->Outlines->Count->value;
        }
    }

    /**
     * Load JavaScript
     *
     * Populates the _javaScript string, for later use of getJavaScript method.
     *
     * @param Zend_Pdf_Element_Reference $root Document catalog entry
     */
    protected function _loadJavaScript(Zend_Pdf_Element_Reference $root)
    {
        if (null === $root->Names || null === $root->Names->JavaScript
            || null === $root->Names->JavaScript->Names
        ) {
            return;
        }

        foreach ($root->Names->JavaScript->Names->items as $item) {
            if ($item instanceof Zend_Pdf_Element_Reference
                && $item->S->value === 'JavaScript'
            ) {
                $this->_javaScript[] = $item->JS->value;
            }
        }
    }

    /**
     * Load form fields
     *
     * Populates the _formFields array, for later lookup of fields by name
     *
     * @param Zend_Pdf_Element_Reference $root Document catalog entry
     */
    protected function _loadFormFields(Zend_Pdf_Element_Reference $root)
    {
        if ($root->AcroForm === null || $root->AcroForm->Fields === null) {
            return;
        }

        foreach ($root->AcroForm->Fields->items as $field) {
            /* We only support fields that are textfields and have a name */
            if ($field->FT && $field->FT->value == 'Tx' && $field->T
                && $field->T !== null
            ) {
                $this->_formFields[$field->T->value] = $field;
            }
        }

        if (!$root->AcroForm->NeedAppearances
            || !$root->AcroForm->NeedAppearances->value
        ) {
            /* Ask the .pdf viewer to generate its own appearance data, so we do not have to */
            $root->AcroForm->add(
                new Zend_Pdf_Element_Name('NeedAppearances'),
                new Zend_Pdf_Element_Boolean(true)
            );
            $root->AcroForm->touch();
        }
    }

    /**
     * Retrieves a list with the names of the AcroForm textfields in the PDF
     *
     * @return array of strings
     */
    public function getTextFieldNames()
    {
        return array_keys($this->_formFields);
    }

    /**
     * Sets the value of an AcroForm text field
     *
     * @param string $name Name of textfield
     * @param string $value Value
     * @throws Zend_Pdf_Exception if the textfield does not exist in the pdf
     */
    public function setTextField($name, $value)
    {
        if (!isset($this->_formFields[$name])) {
            throw new Zend_Pdf_Exception(
                "Field '$name' does not exist or is not a textfield"
            );
        }

        /** @var Zend_Pdf_Element $field */
        $field = $this->_formFields[$name];
        $field->add(
            new Zend_Pdf_Element_Name('V'), new Zend_Pdf_Element_String($value)
        );
        $field->touch();
    }

    /**
     * Sets the properties for an AcroForm text field
     *
     * @param string $name
     * @param mixed  $bitmask
     * @throws Zend_Pdf_Exception
     */
    public function setTextFieldProperties($name, $bitmask)
    {
        if (!isset($this->_formFields[$name])) {
            throw new Zend_Pdf_Exception(
                "Field '$name' does not exist or is not a textfield"
            );
        }

        $field = $this->_formFields[$name];
        $field->add(
            new Zend_Pdf_Element_Name('Ff'),
            new Zend_Pdf_Element_Numeric($bitmask)
        );
        $field->touch();
    }

    /**
     * Marks an AcroForm text field as read only
     *
     * @param string $name
     */
    public function markTextFieldAsReadOnly($name)
    {
        $this->setTextFieldProperties($name, self::PDF_FORM_FIELD_READONLY);
    }

    /**
     * Orginize pages to tha pages tree structure.
     *
     * @todo atomatically attach page to the document, if it's not done yet.
     * @todo check, that page is attached to the current document
     *
     * @todo Dump pages as a balanced tree instead of a plain set.
     */
    protected function _dumpPages()
    {
        $root = $this->_trailer->Root;
        $pagesContainer = $root->Pages;

        $pagesContainer->touch();
        $pagesContainer->Kids->items = array();

        foreach ($this->pages as $page ) {
            $page->render($this->_objFactory);

            $pageDictionary = $page->getPageDictionary();
            $pageDictionary->touch();
            $pageDictionary->Parent = $pagesContainer;

            $pagesContainer->Kids->items[] = $pageDictionary;
        }

        $this->_refreshPagesHash();

        $pagesContainer->Count->touch();
        $pagesContainer->Count->value = count($this->pages);


        // Refresh named destinations list
        foreach ($this->_namedTargets as $name => $namedTarget) {
            if ($namedTarget instanceof Zend_Pdf_Destination_Explicit) {
                // Named target is an explicit destination
                if ($this->resolveDestination($namedTarget, false) === null) {
                    unset($this->_namedTargets[$name]);
                }
            } else if ($namedTarget instanceof Zend_Pdf_Action) {
                // Named target is an action
                if ($this->_cleanUpAction($namedTarget, false) === null) {
                    // Action is a GoTo action with an unresolved destination
                    unset($this->_namedTargets[$name]);
                }
            } else {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Wrong type of named targed (\'' . get_class($namedTarget) . '\').');
            }
        }

        // Refresh outlines
        #require_once 'Zend/Pdf/RecursivelyIteratableObjectsContainer.php';
        $iterator = new RecursiveIteratorIterator(new Zend_Pdf_RecursivelyIteratableObjectsContainer($this->outlines), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $outline) {
            $target = $outline->getTarget();

            if ($target !== null) {
                if ($target instanceof Zend_Pdf_Destination) {
                    // Outline target is a destination
                    if ($this->resolveDestination($target, false) === null) {
                        $outline->setTarget(null);
                    }
                } else if ($target instanceof Zend_Pdf_Action) {
                    // Outline target is an action
                    if ($this->_cleanUpAction($target, false) === null) {
                        // Action is a GoTo action with an unresolved destination
                        $outline->setTarget(null);
                    }
                } else {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Wrong outline target.');
                }
            }
        }

        $openAction = $this->getOpenAction();
        if ($openAction !== null) {
            if ($openAction instanceof Zend_Pdf_Action) {
                // OpenAction is an action
                if ($this->_cleanUpAction($openAction, false) === null) {
                    // Action is a GoTo action with an unresolved destination
                    $this->setOpenAction(null);
                }
            } else if ($openAction instanceof Zend_Pdf_Destination) {
                // OpenAction target is a destination
                if ($this->resolveDestination($openAction, false) === null) {
                    $this->setOpenAction(null);
                }
            } else {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('OpenAction has to be either PDF Action or Destination.');
            }
        }
    }

    /**
     * Dump named destinations
     *
     * @todo Create a balanced tree instead of plain structure.
     */
    protected function _dumpNamedDestinations()
    {
        ksort($this->_namedTargets, SORT_STRING);

        $destArrayItems = array();
        foreach ($this->_namedTargets as $name => $destination) {
            $destArrayItems[] = new Zend_Pdf_Element_String($name);

            if ($destination instanceof Zend_Pdf_Target) {
                $destArrayItems[] = $destination->getResource();
            } else {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('PDF named destinations must be a Zend_Pdf_Target object.');
            }
        }
        $destArray = $this->_objFactory->newObject(new Zend_Pdf_Element_Array($destArrayItems));

        $DestTree = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
        $DestTree->Names = $destArray;

        $root = $this->_trailer->Root;

        if ($root->Names === null) {
            $root->touch();
            $root->Names = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
        } else {
            $root->Names->touch();
        }
        $root->Names->Dests = $DestTree;
    }

    /**
     * Dump outlines recursively
     */
    protected function _dumpOutlines()
    {
        $root = $this->_trailer->Root;

        if ($root->Outlines === null) {
            if (count($this->outlines) == 0) {
                return;
            } else {
                $root->Outlines = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
                $root->Outlines->Type = new Zend_Pdf_Element_Name('Outlines');
                $updateOutlinesNavigation = true;
            }
        } else {
            $updateOutlinesNavigation = false;
            if (count($this->_originalOutlines) != count($this->outlines)) {
                // If original and current outlines arrays have different size then outlines list was updated
                $updateOutlinesNavigation = true;
            } else if ( !(array_keys($this->_originalOutlines) === array_keys($this->outlines)) ) {
                // If original and current outlines arrays have different keys (with a glance to an order) then outlines list was updated
                $updateOutlinesNavigation = true;
            } else {
                foreach ($this->outlines as $key => $outline) {
                    if ($this->_originalOutlines[$key] !== $outline) {
                        $updateOutlinesNavigation = true;
                    }
                }
            }
        }

        $lastOutline = null;
        $openOutlinesCount = 0;
        if ($updateOutlinesNavigation) {
            $root->Outlines->touch();
            $root->Outlines->First = null;

            foreach ($this->outlines as $outline) {
                if ($lastOutline === null) {
                    // First pass. Update Outlines dictionary First entry using corresponding value
                    $lastOutline = $outline->dumpOutline($this->_objFactory, $updateOutlinesNavigation, $root->Outlines);
                    $root->Outlines->First = $lastOutline;
                } else {
                    // Update previous outline dictionary Next entry (Prev is updated within dumpOutline() method)
                    $currentOutlineDictionary = $outline->dumpOutline($this->_objFactory, $updateOutlinesNavigation, $root->Outlines, $lastOutline);
                    $lastOutline->Next = $currentOutlineDictionary;
                    $lastOutline       = $currentOutlineDictionary;
                }
                $openOutlinesCount += $outline->openOutlinesCount();
            }

            $root->Outlines->Last  = $lastOutline;
        } else {
            foreach ($this->outlines as $outline) {
                $lastOutline = $outline->dumpOutline($this->_objFactory, $updateOutlinesNavigation, $root->Outlines, $lastOutline);
                $openOutlinesCount += $outline->openOutlinesCount();
            }
        }

        if ($openOutlinesCount != $this->_originalOpenOutlinesCount) {
            $root->Outlines->touch;
            $root->Outlines->Count = new Zend_Pdf_Element_Numeric($openOutlinesCount);
        }
    }

    /**
     * Create page object, attached to the PDF document.
     * Method signatures:
     *
     * 1. Create new page with a specified pagesize.
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(string $pagesize);
     * ---------------------------------------------------------
     *
     * 2. Create new page with a specified pagesize (in default user space units).
     *    If $factory is null then it will be created and page must be attached to the document to be
     *    included into output.
     * ---------------------------------------------------------
     * new Zend_Pdf_Page(numeric $width, numeric $height);
     * ---------------------------------------------------------
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return Zend_Pdf_Page
     */
    public function newPage($param1, $param2 = null)
    {
        #require_once 'Zend/Pdf/Page.php';
        if ($param2 === null) {
            return new Zend_Pdf_Page($param1, $this->_objFactory);
        } else {
            return new Zend_Pdf_Page($param1, $param2, $this->_objFactory);
        }
    }

    /**
     * Return the document-level Metadata
     * or null Metadata stream is not presented
     *
     * @return string
     */
    public function getMetadata()
    {
        if ($this->_trailer->Root->Metadata !== null) {
            return $this->_trailer->Root->Metadata->value;
        } else {
            return null;
        }
    }

    /**
     * Sets the document-level Metadata (mast be valid XMP document)
     *
     * @param string $metadata
     */
    public function setMetadata($metadata)
    {
        $metadataObject = $this->_objFactory->newStreamObject($metadata);
        $metadataObject->dictionary->Type    = new Zend_Pdf_Element_Name('Metadata');
        $metadataObject->dictionary->Subtype = new Zend_Pdf_Element_Name('XML');

        $this->_trailer->Root->Metadata = $metadataObject;
        $this->_trailer->Root->touch();
    }

    /**
     * Return the document-level JavaScript
     * or null if there is no JavaScript for this document
     *
     * @return string
     */
    public function getJavaScript()
    {
        return $this->_javaScript;
    }

    /**
     * Get open Action
     * Returns Zend_Pdf_Target (Zend_Pdf_Destination or Zend_Pdf_Action object)
     *
     * @return Zend_Pdf_Target
     */
    public function getOpenAction()
    {
        if ($this->_trailer->Root->OpenAction !== null) {
            #require_once 'Zend/Pdf/Target.php';
            return Zend_Pdf_Target::load($this->_trailer->Root->OpenAction);
        } else {
            return null;
        }
    }

    /**
     * Set open Action which is actually Zend_Pdf_Destination or Zend_Pdf_Action object
     *
     * @param Zend_Pdf_Target $openAction
     * @returns Zend_Pdf
     */
    public function setOpenAction(Zend_Pdf_Target $openAction = null)
    {
        $root = $this->_trailer->Root;
        $root->touch();

        if ($openAction === null) {
            $root->OpenAction = null;
        } else {
            $root->OpenAction = $openAction->getResource();

            if ($openAction instanceof Zend_Pdf_Action)  {
                $openAction->dumpAction($this->_objFactory);
            }
        }

        return $this;
    }

    /**
     * Return an associative array containing all the named destinations (or GoTo actions) in the PDF.
     * Named targets can be used to reference from outside
     * the PDF, ex: 'http://www.something.com/mydocument.pdf#MyAction'
     *
     * @return array
     */
    public function getNamedDestinations()
    {
        return $this->_namedTargets;
    }

    /**
     * Return specified named destination
     *
     * @param string $name
     * @return Zend_Pdf_Destination_Explicit|Zend_Pdf_Action_GoTo
     */
    public function getNamedDestination($name)
    {
        if (isset($this->_namedTargets[$name])) {
            return $this->_namedTargets[$name];
        } else {
            return null;
        }
    }

    /**
     * Set specified named destination
     *
     * @param string                                             $name
     * @param Zend_Pdf_Destination_Explicit|Zend_Pdf_Action_GoTo $destination
     * @throws Zend_Pdf_Exception
     */
    public function setNamedDestination($name, $destination = null)
    {
        if ($destination !== null  &&
            !$destination instanceof Zend_Pdf_Action_GoTo  &&
            !$destination instanceof Zend_Pdf_Destination_Explicit) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('PDF named destination must refer an explicit destination or a GoTo PDF action.');
        }

        if ($destination !== null) {
           $this->_namedTargets[$name] = $destination;
        } else {
            unset($this->_namedTargets[$name]);
        }
    }

    /**
     * Pages collection hash:
     * <page dictionary object hash id> => Zend_Pdf_Page
     *
     * @var SplObjectStorage
     */
    protected $_pageReferences = null;

    /**
     * Pages collection hash:
     * <page number> => Zend_Pdf_Page
     *
     * @var array
     */
    protected $_pageNumbers = null;

    /**
     * Refresh page collection hashes
     *
     * @return Zend_Pdf
     */
    protected function _refreshPagesHash()
    {
        $this->_pageReferences = array();
        $this->_pageNumbers    = array();
        $count = 1;
        foreach ($this->pages as $page) {
            $pageDictionaryHashId = spl_object_hash($page->getPageDictionary()->getObject());
            $this->_pageReferences[$pageDictionaryHashId] = $page;
            $this->_pageNumbers[$count++]                 = $page;
        }

        return $this;
    }

    /**
     * Resolve destination.
     *
     * Returns Zend_Pdf_Page page object or null if destination is not found within PDF document.
     *
     * @param Zend_Pdf_Destination $destination Destination to resolve
     * @param bool $refreshPageCollectionHashes Refresh page collection hashes before processing
     * @return Zend_Pdf_Page|null
     * @throws Zend_Pdf_Exception
     */
    public function resolveDestination(Zend_Pdf_Destination $destination, $refreshPageCollectionHashes = true)
    {
        if ($this->_pageReferences === null  ||  $refreshPageCollectionHashes) {
            $this->_refreshPagesHash();
        }

        if ($destination instanceof Zend_Pdf_Destination_Named) {
            if (!isset($this->_namedTargets[$destination->getName()])) {
                return null;
            }
            $destination = $this->getNamedDestination($destination->getName());

            if ($destination instanceof Zend_Pdf_Action) {
                if (!$destination instanceof Zend_Pdf_Action_GoTo) {
                    return null;
                }
                $destination = $destination->getDestination();
            }

            if (!$destination instanceof Zend_Pdf_Destination_Explicit) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Named destination target has to be an explicit destination.');
            }
        }

        // Named target is an explicit destination
        $pageElement = $destination->getResource()->items[0];

        if ($pageElement->getType() == Zend_Pdf_Element::TYPE_NUMERIC) {
            // Page reference is a PDF number
            if (!isset($this->_pageNumbers[$pageElement->value])) {
                return null;
            }

            return $this->_pageNumbers[$pageElement->value];
        }

        // Page reference is a PDF page dictionary reference
        $pageDictionaryHashId = spl_object_hash($pageElement->getObject());
        if (!isset($this->_pageReferences[$pageDictionaryHashId])) {
            return null;
        }
        return $this->_pageReferences[$pageDictionaryHashId];
    }

    /**
     * Walk through action and its chained actions tree and remove nodes
     * if they are GoTo actions with an unresolved target.
     *
     * Returns null if root node is deleted or updated action overwise.
     *
     * @todo Give appropriate name and make method public
     *
     * @param Zend_Pdf_Action $action
     * @param bool $refreshPageCollectionHashes Refresh page collection hashes before processing
     * @return Zend_Pdf_Action|null
     */
    protected function _cleanUpAction(Zend_Pdf_Action $action, $refreshPageCollectionHashes = true)
    {
        if ($this->_pageReferences === null  ||  $refreshPageCollectionHashes) {
            $this->_refreshPagesHash();
        }

        // Named target is an action
        if ($action instanceof Zend_Pdf_Action_GoTo  &&
            $this->resolveDestination($action->getDestination(), false) === null) {
            // Action itself is a GoTo action with an unresolved destination
            return null;
        }

        // Walk through child actions
        $iterator = new RecursiveIteratorIterator($action, RecursiveIteratorIterator::SELF_FIRST);

        $actionsToClean        = array();
        $deletionCandidateKeys = array();
        foreach ($iterator as $chainedAction) {
            if ($chainedAction instanceof Zend_Pdf_Action_GoTo  &&
                $this->resolveDestination($chainedAction->getDestination(), false) === null) {
                // Some child action is a GoTo action with an unresolved destination
                // Mark it as a candidate for deletion
                $actionsToClean[]        = $iterator->getSubIterator();
                $deletionCandidateKeys[] = $iterator->getSubIterator()->key();
            }
        }
        foreach ($actionsToClean as $id => $action) {
            unset($action->next[$deletionCandidateKeys[$id]]);
        }

        return $action;
    }

    /**
     * Extract fonts attached to the document
     *
     * returns array of Zend_Pdf_Resource_Font_Extracted objects
     *
     * @return array
     * @throws Zend_Pdf_Exception
     */
    public function extractFonts()
    {
        $fontResourcesUnique = array();
        foreach ($this->pages as $page) {
            $pageResources = $page->extractResources();

            if ($pageResources->Font === null) {
                // Page doesn't contain have any font reference
                continue;
            }

            $fontResources = $pageResources->Font;

            foreach ($fontResources->getKeys() as $fontResourceName) {
                $fontDictionary = $fontResources->$fontResourceName;

                if (! ($fontDictionary instanceof Zend_Pdf_Element_Reference  ||
                       $fontDictionary instanceof Zend_Pdf_Element_Object) ) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Font dictionary has to be an indirect object or object reference.');
                }

                $fontResourcesUnique[spl_object_hash($fontDictionary->getObject())] = $fontDictionary;
            }
        }

        $fonts = array();
        #require_once 'Zend/Pdf/Exception.php';
        foreach ($fontResourcesUnique as $resourceId => $fontDictionary) {
            try {
                // Try to extract font
                #require_once 'Zend/Pdf/Resource/Font/Extracted.php';
                $extractedFont = new Zend_Pdf_Resource_Font_Extracted($fontDictionary);

                $fonts[$resourceId] = $extractedFont;
            } catch (Zend_Pdf_Exception $e) {
                if ($e->getMessage() != 'Unsupported font type.') {
                    throw $e;
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
     * @param string $fontName
     * @return Zend_Pdf_Resource_Font_Extracted|null
     * @throws Zend_Pdf_Exception
     */
    public function extractFont($fontName)
    {
        $fontResourcesUnique = array();
        #require_once 'Zend/Pdf/Exception.php';
        foreach ($this->pages as $page) {
            $pageResources = $page->extractResources();

            if ($pageResources->Font === null) {
                // Page doesn't contain have any font reference
                continue;
            }

            $fontResources = $pageResources->Font;

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
                        throw $e;
                    }
                    // Continue searhing
                }
            }
        }

        return null;
    }

    /**
     * Render the completed PDF to a string.
     * If $newSegmentOnly is true and it's not a new document,
     * then only appended part of PDF is returned.
     *
     * @param boolean $newSegmentOnly
     * @param resource $outputStream
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function render($newSegmentOnly = false, $outputStream = null)
    {
        if ($this->_isNewDocument) {
            // Drop full document first time even $newSegmentOnly is set to true
            $newSegmentOnly = false;
            $this->_isNewDocument = false;
        }

        // Save document properties if necessary
        if ($this->properties != $this->_originalProperties) {
            $docInfo = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());

            foreach ($this->properties as $key => $value) {
                switch ($key) {
                    case 'Trapped':
                        switch ($value) {
                            case true:
                                $docInfo->$key = new Zend_Pdf_Element_Name('True');
                                break;

                            case false:
                                $docInfo->$key = new Zend_Pdf_Element_Name('False');
                                break;

                            case null:
                                $docInfo->$key = new Zend_Pdf_Element_Name('Unknown');
                                break;

                            default:
                                #require_once 'Zend/Pdf/Exception.php';
                                throw new Zend_Pdf_Exception('Wrong Trapped document property vale: \'' . $value . '\'. Only true, false and null values are allowed.');
                                break;
                        }

                    case 'CreationDate':
                        // break intentionally omitted
                    case 'ModDate':
                        $docInfo->$key = new Zend_Pdf_Element_String((string)$value);
                        break;

                    case 'Title':
                        // break intentionally omitted
                    case 'Author':
                        // break intentionally omitted
                    case 'Subject':
                        // break intentionally omitted
                    case 'Keywords':
                        // break intentionally omitted
                    case 'Creator':
                        // break intentionally omitted
                    case 'Producer':
                        if (extension_loaded('mbstring') === true) {
                            $detected = mb_detect_encoding($value);
                            if ($detected !== 'ASCII') {
                                $value = "\xfe\xff" . mb_convert_encoding($value, 'UTF-16', $detected);
                            }
                        }
                        $docInfo->$key = new Zend_Pdf_Element_String((string)$value);
                        break;

                    default:
                        // Set property using PDF type based on PHP type
                        $docInfo->$key = Zend_Pdf_Element::phpToPdf($value);
                        break;
                }
            }

            $this->_trailer->Info = $docInfo;
        }

        $this->_dumpPages();
        $this->_dumpNamedDestinations();
        $this->_dumpOutlines();

        // Check, that PDF file was modified
        // File is always modified by _dumpPages() now, but future implementations may eliminate this.
        if (!$this->_objFactory->isModified()) {
            if ($newSegmentOnly) {
                // Do nothing, return
                return '';
            }

            if ($outputStream === null) {
                return $this->_trailer->getPDFString();
            } else {
                $pdfData = $this->_trailer->getPDFString();
                while ( strlen($pdfData) > 0 && ($byteCount = fwrite($outputStream, $pdfData)) != false ) {
                    $pdfData = substr($pdfData, $byteCount);
                }

                return '';
            }
        }

        // offset (from a start of PDF file) of new PDF file segment
        $offset = $this->_trailer->getPDFLength();
        // Last Object number in a list of free objects
        $lastFreeObject = $this->_trailer->getLastFreeObject();

        // Array of cross-reference table subsections
        $xrefTable = array();
        // Object numbers of first objects in each subsection
        $xrefSectionStartNums = array();

        // Last cross-reference table subsection
        $xrefSection = array();
        // Dummy initialization of the first element (specail case - header of linked list of free objects).
        $xrefSection[] = 0;
        $xrefSectionStartNums[] = 0;
        // Object number of last processed PDF object.
        // Used to manage cross-reference subsections.
        // Initialized by zero (specail case - header of linked list of free objects).
        $lastObjNum = 0;

        if ($outputStream !== null) {
            if (!$newSegmentOnly) {
                $pdfData = $this->_trailer->getPDFString();
                while ( strlen($pdfData) > 0 && ($byteCount = fwrite($outputStream, $pdfData)) != false ) {
                    $pdfData = substr($pdfData, $byteCount);
                }
            }
        } else {
            $pdfSegmentBlocks = ($newSegmentOnly) ? array() : array($this->_trailer->getPDFString());
        }

        // Iterate objects to create new reference table
        foreach ($this->_objFactory->listModifiedObjects() as $updateInfo) {
            $objNum = $updateInfo->getObjNum();

            if ($objNum - $lastObjNum != 1) {
                // Save cross-reference table subsection and start new one
                $xrefTable[] = $xrefSection;
                $xrefSection = array();
                $xrefSectionStartNums[] = $objNum;
            }

            if ($updateInfo->isFree()) {
                // Free object cross-reference table entry
                $xrefSection[]  = sprintf("%010d %05d f \n", $lastFreeObject, $updateInfo->getGenNum());
                $lastFreeObject = $objNum;
            } else {
                // In-use object cross-reference table entry
                $xrefSection[]  = sprintf("%010d %05d n \n", $offset, $updateInfo->getGenNum());

                $pdfBlock = $updateInfo->getObjectDump();
                $offset += strlen($pdfBlock);

                if ($outputStream === null) {
                    $pdfSegmentBlocks[] = $pdfBlock;
                } else {
                    while ( strlen($pdfBlock) > 0 && ($byteCount = fwrite($outputStream, $pdfBlock)) != false ) {
                        $pdfBlock = substr($pdfBlock, $byteCount);
                    }
                }
            }
            $lastObjNum = $objNum;
        }
        // Save last cross-reference table subsection
        $xrefTable[] = $xrefSection;

        // Modify first entry (specail case - header of linked list of free objects).
        $xrefTable[0][0] = sprintf("%010d 65535 f \n", $lastFreeObject);

        $xrefTableStr = "xref\n";
        foreach ($xrefTable as $sectId => $xrefSection) {
            $xrefTableStr .= sprintf("%d %d \n", $xrefSectionStartNums[$sectId], count($xrefSection));
            foreach ($xrefSection as $xrefTableEntry) {
                $xrefTableStr .= $xrefTableEntry;
            }
        }

        $this->_trailer->Size->value = $this->_objFactory->getObjectCount();

        $pdfBlock = $xrefTableStr
                 .  $this->_trailer->toString()
                 . "startxref\n" . $offset . "\n"
                 . "%%EOF\n";

        $this->_objFactory->cleanEnumerationShiftCache();

        if ($outputStream === null) {
            $pdfSegmentBlocks[] = $pdfBlock;

            return implode('', $pdfSegmentBlocks);
        } else {
            while ( strlen($pdfBlock) > 0 && ($byteCount = fwrite($outputStream, $pdfBlock)) != false ) {
                $pdfBlock = substr($pdfBlock, $byteCount);
            }

            return '';
        }
    }

    /**
     * Sets the document-level JavaScript
     *
     * Resets and appends
     *
     * @param string|array $javaScript
     */
    public function setJavaScript($javaScript)
    {
        $this->resetJavaScript();

        $this->addJavaScript($javaScript);
    }

    /**
     * Resets the document-level JavaScript
     */
    public function resetJavaScript()
    {
        $this->_javaScript = null;

        $root = $this->_trailer->Root;
        if (null === $root->Names || null === $root->Names->JavaScript) {
            return;
        }
        $root->Names->JavaScript = null;
    }

    /**
     * Appends JavaScript to the document-level JavaScript
     *
     * @param string|array $javaScript
     * @throws Zend_Pdf_Exception
     */
    public function addJavaScript($javaScript)
    {
        if (empty($javaScript)) {
            throw new Zend_Pdf_Exception(
                'JavaScript must be a non empty string or array of strings'
            );
        }

        if (!is_array($javaScript)) {
            $javaScript = array($javaScript);
        }

        if (null === $this->_javaScript) {
            $this->_javaScript = $javaScript;
        } else {
            $this->_javaScript = array_merge($this->_javaScript, $javaScript);
        }

        if (!empty($this->_javaScript)) {
            $items = array();

            foreach ($this->_javaScript as $javaScript) {
                $jsCode = array(
                    'S'  => new Zend_Pdf_Element_Name('JavaScript'),
                    'JS' => new Zend_Pdf_Element_String($javaScript)
                );
                $items[] = new Zend_Pdf_Element_String('EmbeddedJS');
                $items[] = $this->_objFactory->newObject(
                    new Zend_Pdf_Element_Dictionary($jsCode)
                );
            }

            $jsRef = $this->_objFactory->newObject(
                new Zend_Pdf_Element_Dictionary(
                    array('Names' => new Zend_Pdf_Element_Array($items))
                )
            );

            if (null === $this->_trailer->Root->Names) {
                $this->_trailer->Root->Names = new Zend_Pdf_Element_Dictionary();
            }
            $this->_trailer->Root->Names->JavaScript = $jsRef;
        }
    }

    /**
     * Convert date to PDF format (it's close to ASN.1 (Abstract Syntax Notation
     * One) defined in ISO/IEC 8824).
     *
     * @todo This really isn't the best location for this method. It should
     *   probably actually exist as Zend_Pdf_Element_Date or something like that.
     *
     * @todo Address the following E_STRICT issue:
     *   PHP Strict Standards:  date(): It is not safe to rely on the system's
     *   timezone settings. Please use the date.timezone setting, the TZ
     *   environment variable or the date_default_timezone_set() function. In
     *   case you used any of those methods and you are still getting this
     *   warning, you most likely misspelled the timezone identifier.
     *
     * @param integer $timestamp (optional) If omitted, uses the current time.
     * @return string
     */
    public static function pdfDate($timestamp = null)
    {
        if ($timestamp === null) {
            $date = date('\D\:YmdHisO');
        } else {
            $date = date('\D\:YmdHisO', $timestamp);
        }
        return substr_replace($date, '\'', -2, 0) . '\'';
    }

}
