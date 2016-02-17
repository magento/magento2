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


/**
 * Abstract PDF outline representation class
 *
 * @todo Implement an ability to associate an outline item with a structure element (PDF 1.3 feature)
 *
 * @package    Zend_Pdf
 * @subpackage Outlines
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Outline implements RecursiveIterator, Countable
{
    /**
     * True if outline is open.
     *
     * @var boolean
     */
    protected $_open = false;

    /**
     * Array of child outlines (array of Zend_Pdf_Outline objects)
     *
     * @var array
     */
    public $childOutlines = array();


    /**
     * Get outline title.
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Set outline title
     *
     * @param string $title
     * @return Zend_Pdf_Outline
     */
    abstract public function setTitle($title);

    /**
     * Returns true if outline item is open by default
     *
     * @return boolean
     */
    public function isOpen()
    {
        return $this->_open;
    }

    /**
     * Sets 'isOpen' outline flag
     *
     * @param boolean $isOpen
     * @return Zend_Pdf_Outline
     */
    public function setIsOpen($isOpen)
    {
        $this->_open = $isOpen;
        return $this;
    }

    /**
     * Returns true if outline item is displayed in italic
     *
     * @return boolean
     */
    abstract public function isItalic();

    /**
     * Sets 'isItalic' outline flag
     *
     * @param boolean $isItalic
     * @return Zend_Pdf_Outline
     */
    abstract public function setIsItalic($isItalic);

    /**
     * Returns true if outline item is displayed in bold
     *
     * @return boolean
     */
    abstract public function isBold();

    /**
     * Sets 'isBold' outline flag
     *
     * @param boolean $isBold
     * @return Zend_Pdf_Outline
     */
    abstract public function setIsBold($isBold);


    /**
     * Get outline text color.
     *
     * @return Zend_Pdf_Color_Rgb
     */
    abstract public function getColor();

    /**
     * Set outline text color.
     * (null means default color which is black)
     *
     * @param Zend_Pdf_Color_Rgb $color
     * @return Zend_Pdf_Outline
     */
    abstract public function setColor(Zend_Pdf_Color_Rgb $color);

    /**
     * Get outline target.
     *
     * @return Zend_Pdf_Target
     */
    abstract public function getTarget();

    /**
     * Set outline target.
     * Null means no target
     *
     * @param Zend_Pdf_Target|string $target
     * @return Zend_Pdf_Outline
     */
    abstract public function setTarget($target = null);

    /**
     * Get outline options
     *
     * @return array
     */
    public function getOptions()
    {
        return array('title'  => $this->_title,
                     'open'   => $this->_open,
                     'color'  => $this->_color,
                     'italic' => $this->_italic,
                     'bold'   => $this->_bold,
                     'target' => $this->_target);
    }

    /**
     * Set outline options
     *
     * @param array $options
     * @return Zend_Pdf_Action
     * @throws Zend_Pdf_Exception
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'title':
                    $this->setTitle($value);
                    break;

                case 'open':
                    $this->setIsOpen($value);
                    break;

                case 'color':
                    $this->setColor($value);
                    break;
                case 'italic':
                    $this->setIsItalic($value);
                    break;

                case 'bold':
                    $this->setIsBold($value);
                    break;

                case 'target':
                    $this->setTarget($value);
                    break;

                default:
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception("Unknown option name - '$key'.");
                    break;
            }
        }

        return $this;
    }

    /**
     * Create new Outline object
     *
     * It provides two forms of input parameters:
     *
     * 1. Zend_Pdf_Outline::create(string $title[, Zend_Pdf_Target $target])
     * 2. Zend_Pdf_Outline::create(array $options)
     *
     * Second form allows to provide outline options as an array.
     * The followed options are supported:
     *   'title'  - string, outline title, required
     *   'open'   - boolean, true if outline entry is open (default value is false)
     *   'color'  - Zend_Pdf_Color_Rgb object, true if outline entry is open (default value is null - black)
     *   'italic' - boolean, true if outline entry is displayed in italic (default value is false)
     *   'bold'   - boolean, true if outline entry is displayed in bold (default value is false)
     *   'target' - Zend_Pdf_Target object or string, outline item destination
     *
     * @return Zend_Pdf_Outline
     * @throws Zend_Pdf_Exception
     */
    public static function create($param1, $param2 = null)
    {
        #require_once 'Zend/Pdf/Outline/Created.php';
        if (is_string($param1)) {
            if ($param2 !== null  &&  !($param2 instanceof Zend_Pdf_Target  ||  is_string($param2))) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Outline create method takes $title (string) and $target (Zend_Pdf_Target or string) or an array as an input');
            }

            return new Zend_Pdf_Outline_Created(array('title'  => $param1,
                                                      'target' => $param2));
        } else {
            if (!is_array($param1)  ||  $param2 !== null) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Outline create method takes $title (string) and $destination (Zend_Pdf_Destination) or an array as an input');
            }

            return new Zend_Pdf_Outline_Created($param1);
        }
    }

    /**
     * Returns number of the total number of open items at all levels of the outline.
     *
     * @internal
     * @return integer
     */
    public function openOutlinesCount()
    {
        $count = 1; // Include this outline

        if ($this->isOpen()) {
            foreach ($this->childOutlines as $child) {
                $count += $child->openOutlinesCount();
            }
        }

        return $count;
    }

    /**
     * Dump Outline and its child outlines into PDF structures
     *
     * Returns dictionary indirect object or reference
     *
     * @param Zend_Pdf_ElementFactory    $factory object factory for newly created indirect objects
     * @param boolean $updateNavigation  Update navigation flag
     * @param Zend_Pdf_Element $parent   Parent outline dictionary reference
     * @param Zend_Pdf_Element $prev     Previous outline dictionary reference
     * @param SplObjectStorage $processedOutlines  List of already processed outlines
     * @return Zend_Pdf_Element
     */
    abstract public function dumpOutline(Zend_Pdf_ElementFactory_Interface $factory,
                                                                           $updateNavigation,
                                                          Zend_Pdf_Element $parent,
                                                          Zend_Pdf_Element $prev = null,
                                                          SplObjectStorage $processedOutlines = null);


    ////////////////////////////////////////////////////////////////////////
    //  RecursiveIterator interface methods
    //////////////

    /**
     * Returns the child outline.
     *
     * @return Zend_Pdf_Outline
     */
    public function current()
    {
        return current($this->childOutlines);
    }

    /**
     * Returns current iterator key
     *
     * @return integer
     */
    public function key()
    {
        return key($this->childOutlines);
    }

    /**
     * Go to next child
     */
    public function next()
    {
        return next($this->childOutlines);
    }

    /**
     * Rewind children
     */
    public function rewind()
    {
        return reset($this->childOutlines);
    }

    /**
     * Check if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return current($this->childOutlines) !== false;
    }

    /**
     * Returns the child outline.
     *
     * @return Zend_Pdf_Outline|null
     */
    public function getChildren()
    {
        return current($this->childOutlines);
    }

    /**
     * Implements RecursiveIterator interface.
     *
     * @return bool  whether container has any pages
     */
    public function hasChildren()
    {
        return count($this->childOutlines) > 0;
    }


    ////////////////////////////////////////////////////////////////////////
    //  Countable interface methods
    //////////////

    /**
     * count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->childOutlines);
    }
}
