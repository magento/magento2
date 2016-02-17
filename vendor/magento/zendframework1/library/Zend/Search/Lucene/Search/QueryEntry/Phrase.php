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

/** Zend_Search_Lucene_Search_QueryEntry */
#require_once 'Zend/Search/Lucene/Search/QueryEntry.php';

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryEntry_Phrase extends Zend_Search_Lucene_Search_QueryEntry
{
    /**
     * Phrase value
     *
     * @var string
     */
    private $_phrase;

    /**
     * Field
     *
     * @var string|null
     */
    private $_field;


    /**
     * Proximity phrase query
     *
     * @var boolean
     */
    private $_proximityQuery = false;

    /**
     * Words distance, used for proximiti queries
     *
     * @var integer
     */
    private $_wordsDistance = 0;


    /**
     * Object constractor
     *
     * @param string $phrase
     * @param string $field
     */
    public function __construct($phrase, $field)
    {
        $this->_phrase = $phrase;
        $this->_field  = $field;
    }

    /**
     * Process modifier ('~')
     *
     * @param mixed $parameter
     */
    public function processFuzzyProximityModifier($parameter = null)
    {
        $this->_proximityQuery = true;

        if ($parameter !== null) {
            $this->_wordsDistance = $parameter;
        }
    }

    /**
     * Transform entry to a subquery
     *
     * @param string $encoding
     * @return Zend_Search_Lucene_Search_Query
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    public function getQuery($encoding)
    {
        /** Zend_Search_Lucene_Search_Query_Preprocessing_Phrase */
        #require_once 'Zend/Search/Lucene/Search/Query/Preprocessing/Phrase.php';
        $query = new Zend_Search_Lucene_Search_Query_Preprocessing_Phrase($this->_phrase,
                                                                          $encoding,
                                                                          ($this->_field !== null)?
                                                                              iconv($encoding, 'UTF-8', $this->_field) :
                                                                              null);

        if ($this->_proximityQuery) {
            $query->setSlop($this->_wordsDistance);
        }

        $query->setBoost($this->_boost);

        return $query;
    }
}
