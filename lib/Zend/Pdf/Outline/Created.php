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
 * @subpackage Actions
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Created.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/** Zend_Pdf_Outline */
#require_once 'Zend/Pdf/Outline.php';

/**
 * PDF outline representation class
 *
 * @todo Implement an ability to associate an outline item with a structure element (PDF 1.3 feature)
 *
 * @package    Zend_Pdf
 * @subpackage Outlines
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Outline_Created extends Zend_Pdf_Outline
{
    /**
     * Outline title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Color to be used for the outline entry’s text.

     * It uses the DeviceRGB color space for color representation.
     * Null means default value - black ([0.0 0.0 0.0] in RGB representation).
     *
     * @var Zend_Pdf_Color_Rgb
     */
    protected $_color = null;

    /**
     * True if outline item is displayed in italic.
     * Default value is false.
     *
     * @var boolean
     */
    protected $_italic = false;

    /**
     * True if outline item is displayed in bold.
     * Default value is false.
     *
     * @var boolean
     */
    protected $_bold = false;

    /**
     * Target destination or action.
     * String means named destination
     *
     * Null means no target.
     *
     * @var Zend_Pdf_Destination|Zend_Pdf_Action
     */
    protected $_target = null;


    /**
     * Get outline title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set outline title
     *
     * @param string $title
     * @return Zend_Pdf_Outline
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * Returns true if outline item is displayed in italic
     *
     * @return boolean
     */
    public function isItalic()
    {
        return $this->_italic;
    }

    /**
     * Sets 'isItalic' outline flag
     *
     * @param boolean $isItalic
     * @return Zend_Pdf_Outline
     */
    public function setIsItalic($isItalic)
    {
        $this->_italic = $isItalic;
        return $this;
    }

    /**
     * Returns true if outline item is displayed in bold
     *
     * @return boolean
     */
    public function isBold()
    {
        return $this->_bold;
    }

    /**
     * Sets 'isBold' outline flag
     *
     * @param boolean $isBold
     * @return Zend_Pdf_Outline
     */
    public function setIsBold($isBold)
    {
        $this->_bold = $isBold;
        return $this;
    }


    /**
     * Get outline text color.
     *
     * @return Zend_Pdf_Color_Rgb
     */
    public function getColor()
    {
        return $this->_color;
    }

    /**
     * Set outline text color.
     * (null means default color which is black)
     *
     * @param Zend_Pdf_Color_Rgb $color
     * @return Zend_Pdf_Outline
     */
    public function setColor(Zend_Pdf_Color_Rgb $color)
    {
        $this->_color = $color;
        return $this;
    }

    /**
     * Get outline target.
     *
     * @return Zend_Pdf_Target
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Set outline target.
     * Null means no target
     *
     * @param Zend_Pdf_Target|string $target
     * @return Zend_Pdf_Outline
     * @throws Zend_Pdf_Exception
     */
    public function setTarget($target = null)
    {
        if (is_string($target)) {
            #require_once 'Zend/Pdf/Destination/Named.php';
            $target = new Zend_Pdf_Destination_Named($target);
        }

        if ($target === null  ||  $target instanceof Zend_Pdf_Target) {
            $this->_target = $target;
        } else {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Outline target has to be Zend_Pdf_Destination or Zend_Pdf_Action object or string');
        }

        return $this;
    }


    /**
     * Object constructor
     *
     * @param array $options
     * @throws Zend_Pdf_Exception
     */
    public function __construct($options = array())
    {
        if (!isset($options['title'])) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Title parameter is required.');
        }

        $this->setOptions($options);
    }

    /**
     * Dump Outline and its child outlines into PDF structures
     *
     * Returns dictionary indirect object or reference
     *
     * @internal
     * @param Zend_Pdf_ElementFactory    $factory object factory for newly created indirect objects
     * @param boolean $updateNavigation  Update navigation flag
     * @param Zend_Pdf_Element $parent   Parent outline dictionary reference
     * @param Zend_Pdf_Element $prev     Previous outline dictionary reference
     * @param SplObjectStorage $processedOutlines  List of already processed outlines
     * @return Zend_Pdf_Element
     * @throws Zend_Pdf_Exception
     */
    public function dumpOutline(Zend_Pdf_ElementFactory_Interface $factory,
                                                                  $updateNavigation,
                                                 Zend_Pdf_Element $parent,
                                                 Zend_Pdf_Element $prev = null,
                                                 SplObjectStorage $processedOutlines = null)
    {
        if ($processedOutlines === null) {
            $processedOutlines = new SplObjectStorage();
        }
        $processedOutlines->attach($this);

        $outlineDictionary = $factory->newObject(new Zend_Pdf_Element_Dictionary());

        $outlineDictionary->Title = new Zend_Pdf_Element_String($this->getTitle());

        $target = $this->getTarget();
        if ($target === null) {
            // Do nothing
        } else if ($target instanceof Zend_Pdf_Destination) {
            $outlineDictionary->Dest = $target->getResource();
        } else if ($target instanceof Zend_Pdf_Action) {
            $outlineDictionary->A    = $target->getResource();
        } else {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Outline target has to be Zend_Pdf_Destination, Zend_Pdf_Action object or null');
        }

        $color = $this->getColor();
        if ($color !== null) {
            $components = $color->getComponents();
            $colorComponentElements = array(new Zend_Pdf_Element_Numeric($components[0]),
                                            new Zend_Pdf_Element_Numeric($components[1]),
                                            new Zend_Pdf_Element_Numeric($components[2]));
            $outlineDictionary->C = new Zend_Pdf_Element_Array($colorComponentElements);
        }

        if ($this->isItalic()  ||  $this->isBold()) {
            $outlineDictionary->F = new Zend_Pdf_Element_Numeric(($this->isItalic()? 1 : 0)  |   // Bit 1 - Italic
                                                                 ($this->isBold()?   2 : 0));    // Bit 2 - Bold
        }


        $outlineDictionary->Parent = $parent;
        $outlineDictionary->Prev   = $prev;

        $lastChild = null;
        foreach ($this->childOutlines as $childOutline) {
            if ($processedOutlines->contains($childOutline)) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Outlines cyclyc reference is detected.');
            }

            if ($lastChild === null) {
                $lastChild = $childOutline->dumpOutline($factory, true, $outlineDictionary, null, $processedOutlines);
                $outlineDictionary->First = $lastChild;
            } else {
                $childOutlineDictionary = $childOutline->dumpOutline($factory, true, $outlineDictionary, $lastChild, $processedOutlines);
                $lastChild->Next = $childOutlineDictionary;
                $lastChild       = $childOutlineDictionary;
            }
        }
        $outlineDictionary->Last = $lastChild;

        if (count($this->childOutlines) != 0) {
            $outlineDictionary->Count = new Zend_Pdf_Element_Numeric(($this->isOpen()? 1 : -1)*count($this->childOutlines));
        }

        return $outlineDictionary;
    }
}
