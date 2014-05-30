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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Markup.php 20785 2010-01-31 09:43:03Z mikaelkael $
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
 * A markup annotation
 *
 * @package    Zend_Pdf
 * @subpackage Annotation
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Annotation_Markup extends Zend_Pdf_Annotation
{
    /**
     * Annotation subtypes
     */
    const SUBTYPE_HIGHLIGHT = 'Highlight';
    const SUBTYPE_UNDERLINE = 'Underline';
    const SUBTYPE_SQUIGGLY  = 'Squiggly';
    const SUBTYPE_STRIKEOUT = 'StrikeOut';

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
            !in_array( $annotationDictionary->Subtype->value,
                       array(self::SUBTYPE_HIGHLIGHT,
                             self::SUBTYPE_UNDERLINE,
                             self::SUBTYPE_SQUIGGLY,
                             self::SUBTYPE_STRIKEOUT) )) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Subtype => Markup entry is omitted or has wrong value.');
        }

        parent::__construct($annotationDictionary);
    }

    /**
     * Create markup annotation object
     *
     * Text markup annotations appear as highlights, underlines, strikeouts or
     * jagged ("squiggly") underlines in the text of a document. When opened,
     * they display a pop-up window containing the text of the associated note.
     *
     * $subType parameter may contain
     *     Zend_Pdf_Annotation_Markup::SUBTYPE_HIGHLIGHT
     *     Zend_Pdf_Annotation_Markup::SUBTYPE_UNDERLINE
     *     Zend_Pdf_Annotation_Markup::SUBTYPE_SQUIGGLY
     *     Zend_Pdf_Annotation_Markup::SUBTYPE_STRIKEOUT
     * for for a highlight, underline, squiggly-underline, or strikeout annotation,
     * respectively.
     *
     * $quadPoints is an array of 8xN numbers specifying the coordinates of
     * N quadrilaterals default user space. Each quadrilateral encompasses a word or
     * group of contiguous words in the text underlying the annotation.
     * The coordinates for each quadrilateral are given in the order
     *     x1 y1 x2 y2 x3 y3 x4 y4
     * specifying the quadrilateral’s four vertices in counterclockwise order
     * starting from left bottom corner.
     * The text is oriented with respect to the edge connecting points
     * (x1, y1) and (x2, y2).
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param string $text
     * @param string $subType
     * @param array $quadPoints  [x1 y1 x2 y2 x3 y3 x4 y4]
     * @return Zend_Pdf_Annotation_Markup
     * @throws Zend_Pdf_Exception
     */
    public static function create($x1, $y1, $x2, $y2, $text, $subType, $quadPoints)
    {
        $annotationDictionary = new Zend_Pdf_Element_Dictionary();

        $annotationDictionary->Type    = new Zend_Pdf_Element_Name('Annot');
        $annotationDictionary->Subtype = new Zend_Pdf_Element_Name($subType);

        $rectangle = new Zend_Pdf_Element_Array();
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($x1);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($y1);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($x2);
        $rectangle->items[] = new Zend_Pdf_Element_Numeric($y2);
        $annotationDictionary->Rect = $rectangle;

        $annotationDictionary->Contents = new Zend_Pdf_Element_String($text);

        if (!is_array($quadPoints)  ||  count($quadPoints) == 0  ||  count($quadPoints) % 8 != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('$quadPoints parameter must be an array of 8xN numbers');
        }
        $points = new Zend_Pdf_Element_Array();
        foreach ($quadPoints as $quadPoint) {
            $points->items[] = new Zend_Pdf_Element_Numeric($quadPoint);
        }
        $annotationDictionary->QuadPoints = $points;

        return new Zend_Pdf_Annotation_Markup($annotationDictionary);
    }
}
