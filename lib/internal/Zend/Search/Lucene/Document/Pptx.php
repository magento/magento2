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
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Pptx.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Document_OpenXml */
#require_once 'Zend/Search/Lucene/Document/OpenXml.php';

/**
 * Pptx document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Document_Pptx extends Zend_Search_Lucene_Document_OpenXml
{
    /**
     * Xml Schema - PresentationML
     *
     * @var string
     */
    const SCHEMA_PRESENTATIONML = 'http://schemas.openxmlformats.org/presentationml/2006/main';

    /**
     * Xml Schema - DrawingML
     *
     * @var string
     */
    const SCHEMA_DRAWINGML = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    /**
     * Xml Schema - Slide relation
     *
     * @var string
     */
    const SCHEMA_SLIDERELATION = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide';

    /**
     * Xml Schema - Slide notes relation
     *
     * @var string
     */
    const SCHEMA_SLIDENOTESRELATION = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/notesSlide';

    /**
     * Object constructor
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @throws Zend_Search_Lucene_Exception
     */
    private function __construct($fileName, $storeContent)
    {
        if (!class_exists('ZipArchive', false)) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('MS Office documents processing functionality requires Zip extension to be loaded');
        }

        // Document data holders
        $slides = array();
        $slideNotes = array();
        $documentBody = array();
        $coreProperties = array();

        // Open OpenXML package
        $package = new ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $relationsXml = $package->getFromName('_rels/.rels');
        if ($relationsXml === false) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Invalid archive or corrupted .pptx file.');
        }
        $relations = simplexml_load_string($relationsXml);
        foreach ($relations->Relationship as $rel) {
            if ($rel["Type"] == Zend_Search_Lucene_Document_OpenXml::SCHEMA_OFFICEDOCUMENT) {
                // Found office document! Search for slides...
                $slideRelations = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/_rels/" . basename($rel["Target"]) . ".rels")) );
                foreach ($slideRelations->Relationship as $slideRel) {
                    if ($slideRel["Type"] == Zend_Search_Lucene_Document_Pptx::SCHEMA_SLIDERELATION) {
                        // Found slide!
                        $slides[ str_replace( 'rId', '', (string)$slideRel["Id"] ) ] = simplexml_load_string(
                            $package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/" . basename($slideRel["Target"])) )
                        );

                        // Search for slide notes
                        $slideNotesRelations = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/_rels/" . basename($slideRel["Target"]) . ".rels")) );
                        foreach ($slideNotesRelations->Relationship as $slideNoteRel) {
                            if ($slideNoteRel["Type"] == Zend_Search_Lucene_Document_Pptx::SCHEMA_SLIDENOTESRELATION) {
                                // Found slide notes!
                                $slideNotes[ str_replace( 'rId', '', (string)$slideRel["Id"] ) ] = simplexml_load_string(
                                    $package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/" . dirname($slideNoteRel["Target"]) . "/" . basename($slideNoteRel["Target"])) )
                                );

                                break;
                            }
                        }
                    }
                }

                break;
            }
        }

        // Sort slides
        ksort($slides);
        ksort($slideNotes);

        // Extract contents from slides
        foreach ($slides as $slideKey => $slide) {
            // Register namespaces
            $slide->registerXPathNamespace("p", Zend_Search_Lucene_Document_Pptx::SCHEMA_PRESENTATIONML);
            $slide->registerXPathNamespace("a", Zend_Search_Lucene_Document_Pptx::SCHEMA_DRAWINGML);

            // Fetch all text
            $textElements = $slide->xpath('//a:t');
            foreach ($textElements as $textElement) {
                $documentBody[] = (string)$textElement;
            }

            // Extract contents from slide notes
            if (isset($slideNotes[$slideKey])) {
                // Fetch slide note
                $slideNote = $slideNotes[$slideKey];

                // Register namespaces
                $slideNote->registerXPathNamespace("p", Zend_Search_Lucene_Document_Pptx::SCHEMA_PRESENTATIONML);
                $slideNote->registerXPathNamespace("a", Zend_Search_Lucene_Document_Pptx::SCHEMA_DRAWINGML);

                // Fetch all text
                $textElements = $slideNote->xpath('//a:t');
                foreach ($textElements as $textElement) {
                    $documentBody[] = (string)$textElement;
                }
            }
        }

        // Read core properties
        $coreProperties = $this->extractMetaData($package);

        // Close file
        $package->close();

        // Store filename
        $this->addField(Zend_Search_Lucene_Field::Text('filename', $fileName, 'UTF-8'));

            // Store contents
        if ($storeContent) {
            $this->addField(Zend_Search_Lucene_Field::Text('body', implode(' ', $documentBody), 'UTF-8'));
        } else {
            $this->addField(Zend_Search_Lucene_Field::UnStored('body', implode(' ', $documentBody), 'UTF-8'));
        }

        // Store meta data properties
        foreach ($coreProperties as $key => $value)
        {
            $this->addField(Zend_Search_Lucene_Field::Text($key, $value, 'UTF-8'));
        }

        // Store title (if not present in meta data)
        if (!isset($coreProperties['title']))
        {
            $this->addField(Zend_Search_Lucene_Field::Text('title', $fileName, 'UTF-8'));
        }
    }

    /**
     * Load Pptx document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @return Zend_Search_Lucene_Document_Pptx
     */
    public static function loadPptxFile($fileName, $storeContent = false)
    {
        return new Zend_Search_Lucene_Document_Pptx($fileName, $storeContent);
    }
}
