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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element.php';
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String.php';


/** Zend_Pdf_Outline */
#require_once 'Zend/Pdf/Outline.php';

/**
 * Traceable PDF outline representation class
 *
 * Instances of this class trace object update uperations. That allows to avoid outlines PDF tree update
 * which should be performed at each document update otherwise.
 *
 * @package    Zend_Pdf
 * @subpackage Outlines
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Outline_Loaded extends Zend_Pdf_Outline
{
    /**
     * Outline dictionary object
     *
     * @var Zend_Pdf_Element_Dictionary|Zend_Pdf_Element_Object|Zend_Pdf_Element_Reference
     */
    protected $_outlineDictionary;

    /**
     * original array of child outlines
     *
     * @var array
     */
    protected $_originalChildOutlines = array();

    /**
     * Get outline title.
     *
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function getTitle()
    {
        if ($this->_outlineDictionary->Title === null) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Outline dictionary Title entry is required.');
        }
        return $this->_outlineDictionary->Title->value;
    }

    /**
     * Set outline title
     *
     * @param string $title
     * @return Zend_Pdf_Outline
     */
    public function setTitle($title)
    {
        $this->_outlineDictionary->Title->touch();
        $this->_outlineDictionary->Title = new Zend_Pdf_Element_String($title);
        return $this;
    }

    /**
     * Sets 'isOpen' outline flag
     *
     * @param boolean $isOpen
     * @return Zend_Pdf_Outline
     */
    public function setIsOpen($isOpen)
    {
        parent::setIsOpen($isOpen);

        if ($this->_outlineDictionary->Count === null) {
            // Do Nothing.
            return this;
        }

        $childrenCount = $this->_outlineDictionary->Count->value;
        $isOpenCurrentState = ($childrenCount > 0);
        if ($isOpen != $isOpenCurrentState) {
            $this->_outlineDictionary->Count->touch();
            $this->_outlineDictionary->Count->value = ($isOpen? 1 : -1)*abs($childrenCount);
        }

        return $this;
    }

    /**
     * Returns true if outline item is displayed in italic
     *
     * @return boolean
     */
    public function isItalic()
    {
        if ($this->_outlineDictionary->F === null) {
            return false;
        }
        return $this->_outlineDictionary->F->value & 1;
    }

    /**
     * Sets 'isItalic' outline flag
     *
     * @param boolean $isItalic
     * @return Zend_Pdf_Outline
     */
    public function setIsItalic($isItalic)
    {
        if ($this->_outlineDictionary->F === null) {
            $this->_outlineDictionary->touch();
            $this->_outlineDictionary->F = new Zend_Pdf_Element_Numeric($isItalic? 1 : 0);
        } else {
            $this->_outlineDictionary->F->touch();
            if ($isItalic) {
                $this->_outlineDictionary->F->value = $this->_outlineDictionary->F->value | 1;
            } else {
                $this->_outlineDictionary->F->value = $this->_outlineDictionary->F->value | ~1;
            }
        }
        return $this;
    }

    /**
     * Returns true if outline item is displayed in bold
     *
     * @return boolean
     */
    public function isBold()
    {
        if ($this->_outlineDictionary->F === null) {
            return false;
        }
        return $this->_outlineDictionary->F->value & 2;
    }

    /**
     * Sets 'isBold' outline flag
     *
     * @param boolean $isBold
     * @return Zend_Pdf_Outline
     */
    public function setIsBold($isBold)
    {
        if ($this->_outlineDictionary->F === null) {
            $this->_outlineDictionary->touch();
            $this->_outlineDictionary->F = new Zend_Pdf_Element_Numeric($isBold? 2 : 0);
        } else {
            $this->_outlineDictionary->F->touch();
            if ($isBold) {
                $this->_outlineDictionary->F->value = $this->_outlineDictionary->F->value | 2;
            } else {
                $this->_outlineDictionary->F->value = $this->_outlineDictionary->F->value | ~2;
            }
        }
        return $this;
    }


    /**
     * Get outline text color.
     *
     * @return Zend_Pdf_Color_Rgb
     */
    public function getColor()
    {
        if ($this->_outlineDictionary->C === null) {
            return null;
        }

        $components = $this->_outlineDictionary->C->items;

        #require_once 'Zend/Pdf/Color/Rgb.php';
        return new Zend_Pdf_Color_Rgb($components[0], $components[1], $components[2]);
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
        $this->_outlineDictionary->touch();

        if ($color === null) {
            $this->_outlineDictionary->C = null;
        } else {
            $components = $color->getComponents();
            $colorComponentElements = array(new Zend_Pdf_Element_Numeric($components[0]),
                                            new Zend_Pdf_Element_Numeric($components[1]),
                                            new Zend_Pdf_Element_Numeric($components[2]));
            $this->_outlineDictionary->C = new Zend_Pdf_Element_Array($colorComponentElements);
        }

        return $this;
    }

    /**
     * Get outline target.
     *
     * @return Zend_Pdf_Target
     * @throws Zend_Pdf_Exception
     */
    public function getTarget()
    {
        if ($this->_outlineDictionary->Dest !== null) {
            if ($this->_outlineDictionary->A !== null) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Outline dictionary may contain Dest or A entry, but not both.');
            }

            #require_once 'Zend/Pdf/Destination.php';
            return Zend_Pdf_Destination::load($this->_outlineDictionary->Dest);
        } else if ($this->_outlineDictionary->A !== null) {
            #require_once 'Zend/Pdf/Action.php';
            return Zend_Pdf_Action::load($this->_outlineDictionary->A);
        }

        return null;
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
        $this->_outlineDictionary->touch();

        if (is_string($target)) {
            #require_once 'Zend/Pdf/Destination/Named.php';
            $target = Zend_Pdf_Destination_Named::create($target);
        }

        if ($target === null) {
            $this->_outlineDictionary->Dest = null;
            $this->_outlineDictionary->A    = null;
        } else if ($target instanceof Zend_Pdf_Destination) {
            $this->_outlineDictionary->Dest = $target->getResource();
            $this->_outlineDictionary->A    = null;
        } else if ($target instanceof Zend_Pdf_Action) {
            $this->_outlineDictionary->Dest = null;
            $this->_outlineDictionary->A    = $target->getResource();
        } else {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Outline target has to be Zend_Pdf_Destination or Zend_Pdf_Action object or string');
        }

        return $this;
    }

    /**
     * Set outline options
     *
     * @param array $options
     * @return Zend_Pdf_Actions_Traceable
     * @throws Zend_Pdf_Exception
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        return $this;
    }



    /**
     * Create PDF outline object using specified dictionary
     *
     * @internal
     * @param Zend_Pdf_Element $dictionary (It's actually Dictionary or Dictionary Object or Reference to a Dictionary Object)
     * @param Zend_Pdf_Action  $parentAction
     * @param SplObjectStorage $processedOutlines  List of already processed Outline dictionaries,
     *                                             used to avoid cyclic references
     * @return Zend_Pdf_Action
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_Element $dictionary, SplObjectStorage $processedDictionaries = null)
    {
        if ($dictionary->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('$dictionary mast be an indirect dictionary object.');
        }

        if ($processedDictionaries === null) {
            $processedDictionaries = new SplObjectStorage();
        }
        $processedDictionaries->attach($dictionary);

        $this->_outlineDictionary = $dictionary;

        if ($dictionary->Count !== null) {
            if ($dictionary->Count->getType() != Zend_Pdf_Element::TYPE_NUMERIC) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Outline dictionary Count entry must be a numeric element.');
            }

            $childOutlinesCount = $dictionary->Count->value;
            if ($childOutlinesCount > 0) {
                $this->_open = true;
            }
            $childOutlinesCount = abs($childOutlinesCount);

            $childDictionary = $dictionary->First;

            $children = new SplObjectStorage();
            while ($childDictionary !== null) {
                // Check children structure for cyclic references
                if ($children->contains($childDictionary)) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Outline childs load error.');
                }

                if (!$processedDictionaries->contains($childDictionary)) {
                    $this->childOutlines[] = new Zend_Pdf_Outline_Loaded($childDictionary, $processedDictionaries);
                }

                $childDictionary = $childDictionary->Next;
            }

            $this->_originalChildOutlines = $this->childOutlines;
        }
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

        if ($updateNavigation) {
            $this->_outlineDictionary->touch();

            $this->_outlineDictionary->Parent = $parent;
            $this->_outlineDictionary->Prev   = $prev;
            $this->_outlineDictionary->Next   = null;
        }

        $updateChildNavigation = false;
        if (count($this->_originalChildOutlines) != count($this->childOutlines)) {
            // If original and current children arrays have different size then children list was updated
            $updateChildNavigation = true;
        } else if ( !(array_keys($this->_originalChildOutlines) === array_keys($this->childOutlines)) ) {
            // If original and current children arrays have different keys (with a glance to an order) then children list was updated
            $updateChildNavigation = true;
        } else {
            foreach ($this->childOutlines as $key => $childOutline) {
                if ($this->_originalChildOutlines[$key] !== $childOutline) {
                    $updateChildNavigation = true;
                    break;
                }
            }
        }

        $lastChild = null;
        if ($updateChildNavigation) {
            $this->_outlineDictionary->touch();
            $this->_outlineDictionary->First = null;

            foreach ($this->childOutlines as $childOutline) {
                if ($processedOutlines->contains($childOutline)) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Outlines cyclyc reference is detected.');
                }

                if ($lastChild === null) {
                    // First pass. Update Outlines dictionary First entry using corresponding value
                    $lastChild = $childOutline->dumpOutline($factory, $updateChildNavigation, $this->_outlineDictionary, null, $processedOutlines);
                    $this->_outlineDictionary->First = $lastChild;
                } else {
                    // Update previous outline dictionary Next entry (Prev is updated within dumpOutline() method)
                    $childOutlineDictionary = $childOutline->dumpOutline($factory, $updateChildNavigation, $this->_outlineDictionary, $lastChild, $processedOutlines);
                    $lastChild->Next = $childOutlineDictionary;
                    $lastChild       = $childOutlineDictionary;
                }
            }

            $this->_outlineDictionary->Last  = $lastChild;

            if (count($this->childOutlines) != 0) {
                $this->_outlineDictionary->Count = new Zend_Pdf_Element_Numeric(($this->isOpen()? 1 : -1)*count($this->childOutlines));
            } else {
                $this->_outlineDictionary->Count = null;
            }
        } else {
            foreach ($this->childOutlines as $childOutline) {
                if ($processedOutlines->contains($childOutline)) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Outlines cyclyc reference is detected.');
                }
                $lastChild = $childOutline->dumpOutline($factory, $updateChildNavigation, $this->_outlineDictionary, $lastChild, $processedOutlines);
            }
        }

        return $this->_outlineDictionary;
    }

    public function dump($level = 0)
    {
        printf(":%3d:%s:%s:%s%s  :\n", count($this->childOutlines),$this->isItalic()? 'i':' ', $this->isBold()? 'b':' ', str_pad('', 4*$level), $this->getTitle());

        if ($this->isOpen()  ||  true) {
            foreach ($this->childOutlines as $child) {
                $child->dump($level + 1);
            }
        }
    }
}
