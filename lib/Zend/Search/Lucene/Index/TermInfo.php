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
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TermInfo.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * A Zend_Search_Lucene_Index_TermInfo represents a record of information stored for a term.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_TermInfo
{
    /**
     * The number of documents which contain the term.
     *
     * @var integer
     */
    public $docFreq;

    /**
     * Data offset in a Frequencies file.
     *
     * @var integer
     */
    public $freqPointer;

    /**
     * Data offset in a Positions file.
     *
     * @var integer
     */
    public $proxPointer;

    /**
     * ScipData offset in a Frequencies file.
     *
     * @var integer
     */
    public $skipOffset;

    /**
     * Term offset of the _next_ term in a TermDictionary file.
     * Used only for Term Index
     *
     * @var integer
     */
    public $indexPointer;

    public function __construct($docFreq, $freqPointer, $proxPointer, $skipOffset, $indexPointer = null)
    {
        $this->docFreq      = $docFreq;
        $this->freqPointer  = $freqPointer;
        $this->proxPointer  = $proxPointer;
        $this->skipOffset   = $skipOffset;
        $this->indexPointer = $indexPointer;
    }
}

