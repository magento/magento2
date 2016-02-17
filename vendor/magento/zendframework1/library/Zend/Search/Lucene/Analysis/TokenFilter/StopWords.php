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
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Search_Lucene_Analysis_TokenFilter */
#require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';

/**
 * Token filter that removes stop words. These words must be provided as array (set), example:
 * $stopwords = array('the' => 1, 'an' => '1');
 *
 * We do recommend to provide all words in lowercase and concatenate this class after the lowercase filter.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Search_Lucene_Analysis_TokenFilter_StopWords extends Zend_Search_Lucene_Analysis_TokenFilter
{
    /**
     * Stop Words
     * @var array
     */
    private $_stopSet;

    /**
     * Constructs new instance of this filter.
     *
     * @param array $stopwords array (set) of words that will be filtered out
     */
    public function __construct($stopwords = array()) {
        $this->_stopSet = array_flip($stopwords);
    }

    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param Zend_Search_Lucene_Analysis_Token $srcToken
     * @return Zend_Search_Lucene_Analysis_Token
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken) {
        if (array_key_exists($srcToken->getTermText(), $this->_stopSet)) {
            return null;
        } else {
            return $srcToken;
        }
    }

    /**
     * Fills stopwords set from a text file. Each line contains one stopword, lines with '#' in the first
     * column are ignored (as comments).
     *
     * You can call this method one or more times. New stopwords are always added to current set.
     *
     * @param string $filepath full path for text file with stopwords
     * @throws Zend_Search_Exception When the file doesn`t exists or is not readable.
     */
    public function loadFromFile($filepath = null) {
        if (! $filepath || ! file_exists($filepath)) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('You have to provide valid file path');
        }
        $fd = fopen($filepath, "r");
        if (! $fd) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Cannot open file ' . $filepath);
        }
        while (!feof ($fd)) {
            $buffer = trim(fgets($fd));
            if (strlen($buffer) > 0 && $buffer[0] != '#') {
                $this->_stopSet[$buffer] = 1;
            }
        }
        if (!fclose($fd)) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Cannot close file ' . $filepath);
        }
    }
}

