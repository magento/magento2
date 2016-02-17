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
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * A field is a section of a Document.  Each field has two parts,
 * a name and a value. Values may be free text or they may be atomic
 * keywords, which are not further processed. Such keywords may
 * be used to represent dates, urls, etc.  Fields are optionally
 * stored in the index, so that they may be returned with hits
 * on the document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Field
{
    /**
     * Field name
     *
     * @var string
     */
    public $name;

    /**
     * Field value
     *
     * @var boolean
     */
    public $value;

    /**
     * Field is to be stored in the index for return with search hits.
     *
     * @var boolean
     */
    public $isStored    = false;

    /**
     * Field is to be indexed, so that it may be searched on.
     *
     * @var boolean
     */
    public $isIndexed   = true;

    /**
     * Field should be tokenized as text prior to indexing.
     *
     * @var boolean
     */
    public $isTokenized = true;
    /**
     * Field is stored as binary.
     *
     * @var boolean
     */
    public $isBinary    = false;

    /**
     * Field are stored as a term vector
     *
     * @var boolean
     */
    public $storeTermVector = false;

    /**
     * Field boost factor
     * It's not stored directly in the index, but affects on normalization factor
     *
     * @var float
     */
    public $boost = 1.0;

    /**
     * Field value encoding.
     *
     * @var string
     */
    public $encoding;

    /**
     * Object constructor
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @param boolean $isStored
     * @param boolean $isIndexed
     * @param boolean $isTokenized
     * @param boolean $isBinary
     */
    public function __construct($name, $value, $encoding, $isStored, $isIndexed, $isTokenized, $isBinary = false)
    {
        $this->name  = $name;
        $this->value = $value;

        if (!$isBinary) {
            $this->encoding    = $encoding;
            $this->isTokenized = $isTokenized;
        } else {
            $this->encoding    = '';
            $this->isTokenized = false;
        }

        $this->isStored  = $isStored;
        $this->isIndexed = $isIndexed;
        $this->isBinary  = $isBinary;

        $this->storeTermVector = false;
        $this->boost           = 1.0;
    }


    /**
     * Constructs a String-valued Field that is not tokenized, but is indexed
     * and stored.  Useful for non-text fields, e.g. date or url.
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @return Zend_Search_Lucene_Field
     */
    public static function keyword($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, true, false);
    }


    /**
     * Constructs a String-valued Field that is not tokenized nor indexed,
     * but is stored in the index, for return with hits.
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @return Zend_Search_Lucene_Field
     */
    public static function unIndexed($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, false, false);
    }


    /**
     * Constructs a Binary String valued Field that is not tokenized nor indexed,
     * but is stored in the index, for return with hits.
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @return Zend_Search_Lucene_Field
     */
    public static function binary($name, $value)
    {
        return new self($name, $value, '', true, false, false, true);
    }

    /**
     * Constructs a String-valued Field that is tokenized and indexed,
     * and is stored in the index, for return with hits.  Useful for short text
     * fields, like "title" or "subject". Term vector will not be stored for this field.
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @return Zend_Search_Lucene_Field
     */
    public static function text($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, true, true);
    }


    /**
     * Constructs a String-valued Field that is tokenized and indexed,
     * but that is not stored in the index.
     *
     * @param string $name
     * @param string $value
     * @param string $encoding
     * @return Zend_Search_Lucene_Field
     */
    public static function unStored($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, false, true, true);
    }

    /**
     * Get field value in UTF-8 encoding
     *
     * @return string
     */
    public function getUtf8Value()
    {
        if (strcasecmp($this->encoding, 'utf8' ) == 0  ||
            strcasecmp($this->encoding, 'utf-8') == 0 ) {
                return $this->value;
        } else {

            return (PHP_OS != 'AIX') ? iconv($this->encoding, 'UTF-8', $this->value) : iconv('ISO8859-1', 'UTF-8', $this->value);
        }
    }
}

