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
 * @subpackage Annotation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Internally used classes */
#require_once 'Zend/Pdf/Element.php';
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/** Zend_Pdf_Annotation */
#require_once 'Zend/Pdf/Annotation.php';

/**
 * A file attachment annotation contains a reference to a file,
 * which typically is embedded in the PDF file.
 *
 * @package    Zend_Pdf
 * @subpackage Annotation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Annotation_FileAttachment extends Zend_Pdf_Annotation
{
    /**
     * Annotation object constructor
     *
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_Element $annotationDictionary)
    {
        if ($annotationDictionary->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Annotation dictionary resource has to be a dictionary.');
        }

        if ($annotationDictionary->Subtype === null  ||
            $annotationDictionary->Subtype->getType() != Zend_Pdf_Element::TYPE_NAME  ||
            $annotationDictionary->Subtype->value != 'FileAttachment') {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Subtype => FileAttachment entry is requires');
        }

        parent::__construct($annotationDictionary);
    }

    /**
     * Create link annotation object
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param string $fileSpecification
     * @return Zend_Pdf_Annotation_FileAttachment
     */
    public static function create($x1, $y1, $x2, $y2, $fileSpecification)
    {
        $annotationDictionary = new Zend_Pdf_Element_Dictionary();

        $annotationDictionary->Type    = new Zend_Pdf_Element_Name('Annot');
        $annotationDictionary->Subtype = new Zend_Pdf_Element_Name('FileAttachment');

        $rectangle = new Zend_Pdf_Element_Array();
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($x1);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($y1);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($x2);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($y2);
        $annotationDictionary->Rect = $rectangle;

        $fsDictionary = new Zend_Pdf_Element_Dictionary();
        $fsDictionary->Type = new Zend_Pdf_Element_Name('Filespec');
        $fsDictionary->F    = new Zend_Pdf_Element_String($fileSpecification);

        $annotationDictionary->FS = $fsDictionary;


        return new Zend_Pdf_Annotation_FileAttachment($annotationDictionary);
    }
}
