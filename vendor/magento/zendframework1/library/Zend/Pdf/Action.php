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


/** Zend_Pdf_Target */
#require_once 'Zend/Pdf/Target.php';


/**
 * Abstract PDF action representation class
 *
 * @package    Zend_Pdf
 * @subpackage Actions
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Action extends Zend_Pdf_Target implements RecursiveIterator, Countable
{
    /**
     * Action dictionary
     *
     * @var Zend_Pdf_Element_Dictionary|Zend_Pdf_Element_Object|Zend_Pdf_Element_Reference
     */
    protected $_actionDictionary;


    /**
     * An original list of chained actions
     *
     * @var array  Array of Zend_Pdf_Action objects
     */
    protected $_originalNextList;

    /**
     * A list of next actions in actions tree (used for actions chaining)
     *
     * @var array  Array of Zend_Pdf_Action objects
     */
    public $next = array();

    /**
     * Object constructor
     *
     * @param Zend_Pdf_Element_Dictionary $dictionary
     * @param SplObjectStorage            $processedActions  list of already processed action dictionaries, used to avoid cyclic references
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_Element $dictionary, SplObjectStorage $processedActions)
    {
        #require_once 'Zend/Pdf/Element.php';
        if ($dictionary->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('$dictionary mast be a direct or an indirect dictionary object.');
        }

        $this->_actionDictionary = $dictionary;

        if ($dictionary->Next !== null) {
            if ($dictionary->Next instanceof Zend_Pdf_Element_Dictionary) {
                // Check if dictionary object is not already processed
                if (!$processedActions->contains($dictionary->Next)) {
                    $processedActions->attach($dictionary->Next);
                    $this->next[] = Zend_Pdf_Action::load($dictionary->Next, $processedActions);
                }
            } else if ($dictionary->Next instanceof Zend_Pdf_Element_Array) {
                foreach ($dictionary->Next->items as $chainedActionDictionary) {
                    // Check if dictionary object is not already processed
                    if (!$processedActions->contains($chainedActionDictionary)) {
                        $processedActions->attach($chainedActionDictionary);
                        $this->next[] = Zend_Pdf_Action::load($chainedActionDictionary, $processedActions);
                    }
                }
            } else {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('PDF Action dictionary Next entry must be a dictionary or an array.');
            }
        }

        $this->_originalNextList = $this->next;
    }

    /**
     * Load PDF action object using specified dictionary
     *
     * @internal
     * @param Zend_Pdf_Element $dictionary (It's actually Dictionary or Dictionary Object or Reference to a Dictionary Object)
     * @param SplObjectStorage $processedActions  list of already processed action dictionaries, used to avoid cyclic references
     * @return Zend_Pdf_Action
     * @throws Zend_Pdf_Exception
     */
    public static function load(Zend_Pdf_Element $dictionary, SplObjectStorage $processedActions = null)
    {
        if ($processedActions === null) {
            $processedActions = new SplObjectStorage();
        }

        #require_once 'Zend/Pdf/Element.php';
        if ($dictionary->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('$dictionary mast be a direct or an indirect dictionary object.');
        }
        if (isset($dictionary->Type)  &&  $dictionary->Type->value != 'Action') {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Action dictionary Type entry must be set to \'Action\'.');
        }

        if ($dictionary->S === null) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Action dictionary must contain S entry');
        }

        switch ($dictionary->S->value) {
            case 'GoTo':
                #require_once 'Zend/Pdf/Action/GoTo.php';
                return new Zend_Pdf_Action_GoTo($dictionary, $processedActions);
                break;

            case 'GoToR':
                #require_once 'Zend/Pdf/Action/GoToR.php';
                return new Zend_Pdf_Action_GoToR($dictionary, $processedActions);
                break;

            case 'GoToE':
                #require_once 'Zend/Pdf/Action/GoToE.php';
                return new Zend_Pdf_Action_GoToE($dictionary, $processedActions);
                break;

            case 'Launch':
                #require_once 'Zend/Pdf/Action/Launch.php';
                return new Zend_Pdf_Action_Launch($dictionary, $processedActions);
                break;

            case 'Thread':
                #require_once 'Zend/Pdf/Action/Thread.php';
                return new Zend_Pdf_Action_Thread($dictionary, $processedActions);
                break;

            case 'URI':
                #require_once 'Zend/Pdf/Action/URI.php';
                return new Zend_Pdf_Action_URI($dictionary, $processedActions);
                break;

            case 'Sound':
                #require_once 'Zend/Pdf/Action/Sound.php';
                return new Zend_Pdf_Action_Sound($dictionary, $processedActions);
                break;

            case 'Movie':
                #require_once 'Zend/Pdf/Action/Movie.php';
                return new Zend_Pdf_Action_Movie($dictionary, $processedActions);
                break;

            case 'Hide':
                #require_once 'Zend/Pdf/Action/Hide.php';
                return new Zend_Pdf_Action_Hide($dictionary, $processedActions);
                break;

            case 'Named':
                #require_once 'Zend/Pdf/Action/Named.php';
                return new Zend_Pdf_Action_Named($dictionary, $processedActions);
                break;

            case 'SubmitForm':
                #require_once 'Zend/Pdf/Action/SubmitForm.php';
                return new Zend_Pdf_Action_SubmitForm($dictionary, $processedActions);
                break;

            case 'ResetForm':
                #require_once 'Zend/Pdf/Action/ResetForm.php';
                return new Zend_Pdf_Action_ResetForm($dictionary, $processedActions);
                break;

            case 'ImportData':
                #require_once 'Zend/Pdf/Action/ImportData.php';
                return new Zend_Pdf_Action_ImportData($dictionary, $processedActions);
                break;

            case 'JavaScript':
                #require_once 'Zend/Pdf/Action/JavaScript.php';
                return new Zend_Pdf_Action_JavaScript($dictionary, $processedActions);
                break;

            case 'SetOCGState':
                #require_once 'Zend/Pdf/Action/SetOCGState.php';
                return new Zend_Pdf_Action_SetOCGState($dictionary, $processedActions);
                break;

            case 'Rendition':
                #require_once 'Zend/Pdf/Action/Rendition.php';
                return new Zend_Pdf_Action_Rendition($dictionary, $processedActions);
                break;

            case 'Trans':
                #require_once 'Zend/Pdf/Action/Trans.php';
                return new Zend_Pdf_Action_Trans($dictionary, $processedActions);
                break;

            case 'GoTo3DView':
                #require_once 'Zend/Pdf/Action/GoTo3DView.php';
                return new Zend_Pdf_Action_GoTo3DView($dictionary, $processedActions);
                break;

            default:
                #require_once 'Zend/Pdf/Action/Unknown.php';
                return new Zend_Pdf_Action_Unknown($dictionary, $processedActions);
                break;
        }
    }

    /**
     * Get resource
     *
     * @internal
     * @return Zend_Pdf_Element
     */
    public function getResource()
    {
        return $this->_actionDictionary;
    }

    /**
     * Dump Action and its child actions into PDF structures
     *
     * Returns dictionary indirect object or reference
     *
     * @internal
     * @param Zend_Pdf_ElementFactory $factory   Object factory for newly created indirect objects
     * @param SplObjectStorage $processedActions  list of already processed actions (used to prevent infinity loop caused by cyclic references)
     * @return Zend_Pdf_Element_Object|Zend_Pdf_Element_Reference   Dictionary indirect object
     */
    public function dumpAction(Zend_Pdf_ElementFactory_Interface $factory, SplObjectStorage $processedActions = null)
    {
        if ($processedActions === null) {
            $processedActions = new SplObjectStorage();
        }
        if ($processedActions->contains($this)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Action chain cyclyc reference is detected.');
        }
        $processedActions->attach($this);

        $childListUpdated = false;
        if (count($this->_originalNextList) != count($this->next)) {
            // If original and current children arrays have different size then children list was updated
            $childListUpdated = true;
        } else if ( !(array_keys($this->_originalNextList) === array_keys($this->next)) ) {
            // If original and current children arrays have different keys (with a glance to an order) then children list was updated
            $childListUpdated = true;
        } else {
            foreach ($this->next as $key => $childAction) {
                if ($this->_originalNextList[$key] !== $childAction) {
                    $childListUpdated = true;
                    break;
                }
            }
        }

        if ($childListUpdated) {
            $this->_actionDictionary->touch();
            switch (count($this->next)) {
                case 0:
                    $this->_actionDictionary->Next = null;
                    break;

                case 1:
                    $child = reset($this->next);
                    $this->_actionDictionary->Next = $child->dumpAction($factory, $processedActions);
                    break;

                default:
                    #require_once 'Zend/Pdf/Element/Array.php';
                    $pdfChildArray = new Zend_Pdf_Element_Array();
                    foreach ($this->next as $child) {

                        $pdfChildArray->items[] = $child->dumpAction($factory, $processedActions);
                    }
                    $this->_actionDictionary->Next = $pdfChildArray;
                    break;
            }
        } else {
            foreach ($this->next as $child) {
                $child->dumpAction($factory, $processedActions);
            }
        }

        if ($this->_actionDictionary instanceof Zend_Pdf_Element_Dictionary) {
            // It's a newly created action. Register it within object factory and return indirect object
            return $factory->newObject($this->_actionDictionary);
        } else {
            // It's a loaded object
            return $this->_actionDictionary;
        }
    }


    ////////////////////////////////////////////////////////////////////////
    //  RecursiveIterator interface methods
    //////////////

    /**
     * Returns current child action.
     *
     * @return Zend_Pdf_Action
     */
    public function current()
    {
        return current($this->next);
    }

    /**
     * Returns current iterator key
     *
     * @return integer
     */
    public function key()
    {
        return key($this->next);
    }

    /**
     * Go to next child
     */
    public function next()
    {
        return next($this->next);
    }

    /**
     * Rewind children
     */
    public function rewind()
    {
        return reset($this->next);
    }

    /**
     * Check if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return current($this->next) !== false;
    }

    /**
     * Returns the child action.
     *
     * @return Zend_Pdf_Action|null
     */
    public function getChildren()
    {
        return current($this->next);
    }

    /**
     * Implements RecursiveIterator interface.
     *
     * @return bool  whether container has any pages
     */
    public function hasChildren()
    {
        return count($this->next) > 0;
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
