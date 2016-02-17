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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Dictionary loader
 *
 * It's a dummy class which is created to encapsulate non-good structured code.
 * Manual "method inlining" is performed to increase dictionary index loading operation
 * which is major bottelneck for search performance.
 *
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_DictionaryLoader
{
    /**
     * Dictionary index loader.
     *
     * It takes a string which is actually <segment_name>.tii index file data and
     * returns two arrays - term and tremInfo lists.
     *
     * See Zend_Search_Lucene_Index_SegmintInfo class for details
     *
     * @param string $data
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public static function load($data)
    {
        $termDictionary = array();
        $termInfos      = array();
        $pos = 0;

        // $tiVersion = $tiiFile->readInt();
        $tiVersion = ord($data[0]) << 24 | ord($data[1]) << 16 | ord($data[2]) << 8  | ord($data[3]);
        $pos += 4;
        if ($tiVersion != (int)0xFFFFFFFE /* pre-2.1 format */ &&
            $tiVersion != (int)0xFFFFFFFD /* 2.1+ format    */) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Wrong TermInfoIndexFile file format');
        }

        // $indexTermCount = $tiiFile->readLong();
        if (PHP_INT_SIZE > 4) {
            $indexTermCount = ord($data[$pos]) << 56  |
                              ord($data[$pos+1]) << 48  |
                              ord($data[$pos+2]) << 40  |
                              ord($data[$pos+3]) << 32  |
                              ord($data[$pos+4]) << 24  |
                              ord($data[$pos+5]) << 16  |
                              ord($data[$pos+6]) << 8   |
                              ord($data[$pos+7]);
        } else {
            if ((ord($data[$pos])            != 0) ||
                (ord($data[$pos+1])          != 0) ||
                (ord($data[$pos+2])          != 0) ||
                (ord($data[$pos+3])          != 0) ||
                ((ord($data[$pos+4]) & 0x80) != 0)) {
                    #require_once 'Zend/Search/Lucene/Exception.php';
                    throw new Zend_Search_Lucene_Exception('Largest supported segment size (for 32-bit mode) is 2Gb');
                 }

            $indexTermCount = ord($data[$pos+4]) << 24  |
                              ord($data[$pos+5]) << 16  |
                              ord($data[$pos+6]) << 8   |
                              ord($data[$pos+7]);
        }
        $pos += 8;

        //                  $tiiFile->readInt();  // IndexInterval
        $pos += 4;

        // $skipInterval   = $tiiFile->readInt();
        $skipInterval = ord($data[$pos]) << 24 | ord($data[$pos+1]) << 16 | ord($data[$pos+2]) << 8  | ord($data[$pos+3]);
        $pos += 4;
        if ($indexTermCount < 1) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong number of terms in a term dictionary index');
        }

        if ($tiVersion == (int)0xFFFFFFFD /* 2.1+ format */) {
            /* Skip MaxSkipLevels value */
            $pos += 4;
        }

        $prevTerm     = '';
        $freqPointer  =  0;
        $proxPointer  =  0;
        $indexPointer =  0;
        for ($count = 0; $count < $indexTermCount; $count++) {
            //$termPrefixLength = $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $termPrefixLength = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $termPrefixLength |= ($nbyte & 0x7F) << $shift;
            }

            // $termSuffix       = $tiiFile->readString();
            $nbyte = ord($data[$pos++]);
            $len = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $len |= ($nbyte & 0x7F) << $shift;
            }
            if ($len == 0) {
                $termSuffix = '';
            } else {
                $termSuffix = substr($data, $pos, $len);
                $pos += $len;
                for ($count1 = 0; $count1 < $len; $count1++ ) {
                    if (( ord($termSuffix[$count1]) & 0xC0 ) == 0xC0) {
                        $addBytes = 1;
                        if (ord($termSuffix[$count1]) & 0x20 ) {
                            $addBytes++;

                            // Never used for Java Lucene created index.
                            // Java2 doesn't encode strings in four bytes
                            if (ord($termSuffix[$count1]) & 0x10 ) {
                                $addBytes++;
                            }
                        }
                        $termSuffix .= substr($data, $pos, $addBytes);
                        $pos += $addBytes;
                        $len += $addBytes;

                        // Check for null character. Java2 encodes null character
                        // in two bytes.
                        if (ord($termSuffix[$count1]) == 0xC0 &&
                            ord($termSuffix[$count1+1]) == 0x80   ) {
                            $termSuffix[$count1] = 0;
                            $termSuffix = substr($termSuffix,0,$count1+1)
                                        . substr($termSuffix,$count1+2);
                        }
                        $count1 += $addBytes;
                    }
                }
            }

            // $termValue        = Zend_Search_Lucene_Index_Term::getPrefix($prevTerm, $termPrefixLength) . $termSuffix;
            $pb = 0; $pc = 0;
            while ($pb < strlen($prevTerm)  &&  $pc < $termPrefixLength) {
                $charBytes = 1;
                if ((ord($prevTerm[$pb]) & 0xC0) == 0xC0) {
                    $charBytes++;
                    if (ord($prevTerm[$pb]) & 0x20 ) {
                        $charBytes++;
                        if (ord($prevTerm[$pb]) & 0x10 ) {
                            $charBytes++;
                        }
                    }
                }

                if ($pb + $charBytes > strlen($data)) {
                    // wrong character
                    break;
                }

                $pc++;
                $pb += $charBytes;
            }
            $termValue = substr($prevTerm, 0, $pb) . $termSuffix;

            // $termFieldNum     = $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $termFieldNum = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $termFieldNum |= ($nbyte & 0x7F) << $shift;
            }

            // $docFreq          = $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $docFreq = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $docFreq |= ($nbyte & 0x7F) << $shift;
            }

            // $freqPointer     += $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $vint = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $vint |= ($nbyte & 0x7F) << $shift;
            }
            $freqPointer += $vint;

            // $proxPointer     += $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $vint = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $vint |= ($nbyte & 0x7F) << $shift;
            }
            $proxPointer += $vint;

            if( $docFreq >= $skipInterval ) {
                // $skipDelta = $tiiFile->readVInt();
                $nbyte = ord($data[$pos++]);
                $vint = $nbyte & 0x7F;
                for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                    $nbyte = ord($data[$pos++]);
                    $vint |= ($nbyte & 0x7F) << $shift;
                }
                $skipDelta = $vint;
            } else {
                $skipDelta = 0;
            }

            // $indexPointer += $tiiFile->readVInt();
            $nbyte = ord($data[$pos++]);
            $vint = $nbyte & 0x7F;
            for ($shift=7; ($nbyte & 0x80) != 0; $shift += 7) {
                $nbyte = ord($data[$pos++]);
                $vint |= ($nbyte & 0x7F) << $shift;
            }
            $indexPointer += $vint;


            // $this->_termDictionary[] =  new Zend_Search_Lucene_Index_Term($termValue, $termFieldNum);
            $termDictionary[] = array($termFieldNum, $termValue);

            $termInfos[] =
                 // new Zend_Search_Lucene_Index_TermInfo($docFreq, $freqPointer, $proxPointer, $skipDelta, $indexPointer);
                 array($docFreq, $freqPointer, $proxPointer, $skipDelta, $indexPointer);

            $prevTerm = $termValue;
        }

        // Check special index entry mark
        if ($termDictionary[0][0] != (int)0xFFFFFFFF) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong TermInfoIndexFile file format');
        }

        if (PHP_INT_SIZE > 4) {
            // Treat 64-bit 0xFFFFFFFF as -1
            $termDictionary[0][0] = -1;
        }

        return array($termDictionary, $termInfos);
    }
}

