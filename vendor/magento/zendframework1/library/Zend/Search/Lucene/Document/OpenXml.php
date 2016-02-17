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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Search_Lucene_Document */
#require_once 'Zend/Search/Lucene/Document.php';

/** Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * OpenXML document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Document_OpenXml extends Zend_Search_Lucene_Document
{
    /**
     * Xml Schema - Relationships
     *
     * @var string
     */
    const SCHEMA_RELATIONSHIP = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /**
     * Xml Schema - Office document
     *
     * @var string
     */
    const SCHEMA_OFFICEDOCUMENT = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';

    /**
     * Xml Schema - Core properties
     *
     * @var string
     */
    const SCHEMA_COREPROPERTIES = 'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties';

    /**
     * Xml Schema - Dublin Core
     *
     * @var string
     */
    const SCHEMA_DUBLINCORE = 'http://purl.org/dc/elements/1.1/';

    /**
     * Xml Schema - Dublin Core Terms
     *
     * @var string
     */
    const SCHEMA_DUBLINCORETERMS = 'http://purl.org/dc/terms/';

    /**
     * Extract metadata from document
     *
     * @param ZipArchive $package    ZipArchive OpenXML package
     * @return array    Key-value pairs containing document meta data
     */
    protected function extractMetaData(ZipArchive $package)
    {
        // Data holders
        $coreProperties = array();

        // Read relations and search for core properties
        $relations = Zend_Xml_Security::scan($package->getFromName("_rels/.rels"));
        foreach ($relations->Relationship as $rel) {
            if ($rel["Type"] == Zend_Search_Lucene_Document_OpenXml::SCHEMA_COREPROPERTIES) {
                // Found core properties! Read in contents...
                $contents = Zend_Xml_Security::scan(
                    $package->getFromName(dirname($rel["Target"]) . "/" . basename($rel["Target"]))
                );

                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_DUBLINCORE) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_COREPROPERTIES) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_DUBLINCORETERMS) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
            }
        }

        return $coreProperties;
    }

    /**
     * Determine absolute zip path
     *
     * @param string $path
     * @return string
     */
    protected function absoluteZipPath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }
}
