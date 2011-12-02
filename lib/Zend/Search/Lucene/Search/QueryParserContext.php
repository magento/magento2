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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: QueryParserContext.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Search_Lucene_Search_QueryToken */
#require_once 'Zend/Search/Lucene/Search/QueryToken.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_QueryParserContext
{
    /**
     * Default field for the context.
     *
     * null means, that term should be searched through all fields
     * Zend_Search_Lucene_Search_Query::rewriteQuery($index) transletes such queries to several
     *
     * @var string|null
     */
    private $_defaultField;

    /**
     * Field specified for next entry
     *
     * @var string
     */
    private $_nextEntryField = null;

    /**
     * True means, that term is required.
     * False means, that term is prohibited.
     * null means, that term is neither prohibited, nor required
     *
     * @var boolean
     */
    private $_nextEntrySign = null;


    /**
     * Entries grouping mode
     */
    const GM_SIGNS   = 0;  // Signs mode: '+term1 term2 -term3 +(subquery1) -(subquery2)'
    const GM_BOOLEAN = 1;  // Boolean operators mode: 'term1 and term2  or  (subquery1) and not (subquery2)'

    /**
     * Grouping mode
     *
     * @var integer
     */
    private $_mode = null;

    /**
     * Entries signs.
     * Used in GM_SIGNS grouping mode
     *
     * @var arrays
     */
    private $_signs = array();

    /**
     * Query entries
     * Each entry is a Zend_Search_Lucene_Search_QueryEntry object or
     * boolean operator (Zend_Search_Lucene_Search_QueryToken class constant)
     *
     * @var array
     */
    private $_entries = array();

    /**
     * Query string encoding
     *
     * @var string
     */
    private $_encoding;


    /**
     * Context object constructor
     *
     * @param string $encoding
     * @param string|null $defaultField
     */
    public function __construct($encoding, $defaultField = null)
    {
        $this->_encoding     = $encoding;
        $this->_defaultField = $defaultField;
    }


    /**
     * Get context default field
     *
     * @return string|null
     */
    public function getField()
    {
        return ($this->_nextEntryField !== null)  ?  $this->_nextEntryField : $this->_defaultField;
    }

    /**
     * Set field for next entry
     *
     * @param string $field
     */
    public function setNextEntryField($field)
    {
        $this->_nextEntryField = $field;
    }


    /**
     * Set sign for next entry
     *
     * @param integer $sign
     * @throws Zend_Search_Lucene_Exception
     */
    public function setNextEntrySign($sign)
    {
        if ($this->_mode === self::GM_BOOLEAN) {
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('It\'s not allowed to mix boolean and signs styles in the same subquery.');
        }

        $this->_mode = self::GM_SIGNS;

        if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED) {
            $this->_nextEntrySign = true;
        } else if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED) {
            $this->_nextEntrySign = false;
        } else {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Unrecognized sign type.');
        }
    }


    /**
     * Add entry to a query
     *
     * @param Zend_Search_Lucene_Search_QueryEntry $entry
     */
    public function addEntry(Zend_Search_Lucene_Search_QueryEntry $entry)
    {
        if ($this->_mode !== self::GM_BOOLEAN) {
            $this->_signs[] = $this->_nextEntrySign;
        }

        $this->_entries[] = $entry;

        $this->_nextEntryField = null;
        $this->_nextEntrySign  = null;
    }


    /**
     * Process fuzzy search or proximity search modifier
     *
     * @throws Zend_Search_Lucene_Search_QueryParserException
     */
    public function processFuzzyProximityModifier($parameter = null)
    {
        // Check, that modifier has came just after word or phrase
        if ($this->_nextEntryField !== null  ||  $this->_nextEntrySign !== null) {
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry = array_pop($this->_entries);

        if (!$lastEntry instanceof Zend_Search_Lucene_Search_QueryEntry) {
            // there are no entries or last entry is boolean operator
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry->processFuzzyProximityModifier($parameter);

        $this->_entries[] = $lastEntry;
    }

    /**
     * Set boost factor to the entry
     *
     * @param float $boostFactor
     */
    public function boost($boostFactor)
    {
        // Check, that modifier has came just after word or phrase
        if ($this->_nextEntryField !== null  ||  $this->_nextEntrySign !== null) {
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'^\' modifier must follow word, phrase or subquery.');
        }

        $lastEntry = array_pop($this->_entries);

        if (!$lastEntry instanceof Zend_Search_Lucene_Search_QueryEntry) {
            // there are no entries or last entry is boolean operator
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'^\' modifier must follow word, phrase or subquery.');
        }

        $lastEntry->boost($boostFactor);

        $this->_entries[] = $lastEntry;
    }

    /**
     * Process logical operator
     *
     * @param integer $operator
     */
    public function addLogicalOperator($operator)
    {
        if ($this->_mode === self::GM_SIGNS) {
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('It\'s not allowed to mix boolean and signs styles in the same subquery.');
        }

        $this->_mode = self::GM_BOOLEAN;

        $this->_entries[] = $operator;
    }


    /**
     * Generate 'signs style' query from the context
     * '+term1 term2 -term3 +(<subquery1>) ...'
     *
     * @return Zend_Search_Lucene_Search_Query
     */
    public function _signStyleExpressionQuery()
    {
        #require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';
        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        #require_once 'Zend/Search/Lucene/Search/QueryParser.php';
        if (Zend_Search_Lucene_Search_QueryParser::getDefaultOperator() == Zend_Search_Lucene_Search_QueryParser::B_AND) {
            $defaultSign = true; // required
        } else {
            // Zend_Search_Lucene_Search_QueryParser::B_OR
            $defaultSign = null; // optional
        }

        foreach ($this->_entries as $entryId => $entry) {
            $sign = ($this->_signs[$entryId] !== null) ?  $this->_signs[$entryId] : $defaultSign;
            $query->addSubquery($entry->getQuery($this->_encoding), $sign);
        }

        return $query;
    }


    /**
     * Generate 'boolean style' query from the context
     * 'term1 and term2   or   term3 and (<subquery1>) and not (<subquery2>)'
     *
     * @return Zend_Search_Lucene_Search_Query
     * @throws Zend_Search_Lucene
     */
    private function _booleanExpressionQuery()
    {
        /**
         * We treat each level of an expression as a boolean expression in
         * a Disjunctive Normal Form
         *
         * AND operator has higher precedence than OR
         *
         * Thus logical query is a disjunction of one or more conjunctions of
         * one or more query entries
         */

        #require_once 'Zend/Search/Lucene/Search/BooleanExpressionRecognizer.php';
        $expressionRecognizer = new Zend_Search_Lucene_Search_BooleanExpressionRecognizer();

        #require_once 'Zend/Search/Lucene/Exception.php';
        try {
            foreach ($this->_entries as $entry) {
                if ($entry instanceof Zend_Search_Lucene_Search_QueryEntry) {
                    $expressionRecognizer->processLiteral($entry);
                } else {
                    switch ($entry) {
                        case Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_AND_OPERATOR);
                            break;

                        case Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_OR_OPERATOR);
                            break;

                        case Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_NOT_OPERATOR);
                            break;

                        default:
                            throw new Zend_Search_Lucene('Boolean expression error. Unknown operator type.');
                    }
                }
            }

            $conjuctions = $expressionRecognizer->finishExpression();
        } catch (Zend_Search_Exception $e) {
            // throw new Zend_Search_Lucene_Search_QueryParserException('Boolean expression error. Error message: \'' .
            //                                                          $e->getMessage() . '\'.' );
            // It's query syntax error message and it should be user friendly. So FSM message is omitted
            #require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Boolean expression error.', 0, $e);
        }

        // Remove 'only negative' conjunctions
        foreach ($conjuctions as $conjuctionId => $conjuction) {
            $nonNegativeEntryFound = false;

            foreach ($conjuction as $conjuctionEntry) {
                if ($conjuctionEntry[1]) {
                    $nonNegativeEntryFound = true;
                    break;
                }
            }

            if (!$nonNegativeEntryFound) {
                unset($conjuctions[$conjuctionId]);
            }
        }


        $subqueries = array();
        foreach ($conjuctions as  $conjuction) {
            // Check, if it's a one term conjuction
            if (count($conjuction) == 1) {
                $subqueries[] = $conjuction[0][0]->getQuery($this->_encoding);
            } else {
                #require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';
                $subquery = new Zend_Search_Lucene_Search_Query_Boolean();

                foreach ($conjuction as $conjuctionEntry) {
                    $subquery->addSubquery($conjuctionEntry[0]->getQuery($this->_encoding), $conjuctionEntry[1]);
                }

                $subqueries[] = $subquery;
            }
        }

        if (count($subqueries) == 0) {
            #require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($subqueries) == 1) {
            return $subqueries[0];
        }


        #require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';
        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        foreach ($subqueries as $subquery) {
            // Non-requirered entry/subquery
            $query->addSubquery($subquery);
        }

        return $query;
    }

    /**
     * Generate query from current context
     *
     * @return Zend_Search_Lucene_Search_Query
     */
    public function getQuery()
    {
        if ($this->_mode === self::GM_BOOLEAN) {
            return $this->_booleanExpressionQuery();
        } else {
            return $this->_signStyleExpressionQuery();
        }
    }
}
