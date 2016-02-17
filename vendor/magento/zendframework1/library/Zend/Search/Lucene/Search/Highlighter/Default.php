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
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Search_Lucene_Search_Highlighter_Interface */
#require_once 'Zend/Search/Lucene/Search/Highlighter/Interface.php';
/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Highlighter_Default implements Zend_Search_Lucene_Search_Highlighter_Interface
{
    /**
     * List of colors for text highlighting
     *
     * @var array
     */
    protected $_highlightColors = array('#66ffff', '#ff66ff', '#ffff66',
                                        '#ff8888', '#88ff88', '#8888ff',
                                        '#88dddd', '#dd88dd', '#dddd88',
                                        '#aaddff', '#aaffdd', '#ddaaff',
                                        '#ddffaa', '#ffaadd', '#ffddaa');

    /**
     * Index of current color for highlighting
     *
     * Index is increased at each highlight() call, so terms matching different queries are highlighted using different colors.
     *
     * @var integer
     */
    protected $_currentColorIndex = 0;

    /**
     * HTML document for highlighting
     *
     * @var Zend_Search_Lucene_Document_Html
     */
    protected $_doc;

    /**
     * Set document for highlighting.
     *
     * @param Zend_Search_Lucene_Document_Html $document
     */
    public function setDocument(Zend_Search_Lucene_Document_Html $document)
    {
        $this->_doc = $document;
    }

    /**
     * Get document for highlighting.
     *
     * @return Zend_Search_Lucene_Document_Html $document
     */
    public function getDocument()
    {
        return $this->_doc;
    }

    /**
     * Highlight specified words
     *
     * @param string|array $words  Words to highlight. They could be organized using the array or string.
     */
    public function highlight($words)
    {
        $color = $this->_highlightColors[$this->_currentColorIndex];
        $this->_currentColorIndex = ($this->_currentColorIndex + 1) % count($this->_highlightColors);

        $this->_doc->highlight($words, $color);
    }

}
