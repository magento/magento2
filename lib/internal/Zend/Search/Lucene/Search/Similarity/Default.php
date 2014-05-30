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
 * @version    $Id: Default.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Search_Similarity */
#require_once 'Zend/Search/Lucene/Search/Similarity.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Similarity_Default extends Zend_Search_Lucene_Search_Similarity
{

    /**
     * Implemented as '1/sqrt(numTerms)'.
     *
     * @param string $fieldName
     * @param integer $numTerms
     * @return float
     */
    public function lengthNorm($fieldName, $numTerms)
    {
        if ($numTerms == 0) {
            return 1E10;
        }

        return 1.0/sqrt($numTerms);
    }

    /**
     * Implemented as '1/sqrt(sumOfSquaredWeights)'.
     *
     * @param float $sumOfSquaredWeights
     * @return float
     */
    public function queryNorm($sumOfSquaredWeights)
    {
        return 1.0/sqrt($sumOfSquaredWeights);
    }

    /**
     * Implemented as 'sqrt(freq)'.
     *
     * @param float $freq
     * @return float
     */
    public function tf($freq)
    {
        return sqrt($freq);
    }

    /**
     * Implemented as '1/(distance + 1)'.
     *
     * @param integer $distance
     * @return float
     */
    public function sloppyFreq($distance)
    {
        return 1.0/($distance + 1);
    }

    /**
     * Implemented as 'log(numDocs/(docFreq+1)) + 1'.
     *
     * @param integer $docFreq
     * @param integer $numDocs
     * @return float
     */
    public function idfFreq($docFreq, $numDocs)
    {
        return log($numDocs/(float)($docFreq+1)) + 1.0;
    }

    /**
     * Implemented as 'overlap/maxOverlap'.
     *
     * @param integer $overlap
     * @param integer $maxOverlap
     * @return float
     */
    public function coord($overlap, $maxOverlap)
    {
        return $overlap/(float)$maxOverlap;
    }
}
