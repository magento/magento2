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


/**
 * Calculate query weights and build query scorers.
 *
 * A Weight is constructed by a query Query->createWeight().
 * The sumOfSquaredWeights() method is then called on the top-level
 * query to compute the query normalization factor Similarity->queryNorm(float).
 * This factor is then passed to normalize(float).  At this point the weighting
 * is complete.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Search_Weight
{
    /**
     * Normalization factor.
     * This value is stored only for query expanation purpose and not used in any other place
     *
     * @var float
     */
    protected $_queryNorm;

    /**
     * Weight value
     *
     * Weight value may be initialized in sumOfSquaredWeights() or normalize()
     * because they both are invoked either in Query::_initWeight (for top-level query) or
     * in corresponding methods of parent query's weights
     *
     * @var float
     */
    protected $_value;


    /**
     * The weight for this query.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * The sum of squared weights of contained query clauses.
     *
     * @return float
     */
    abstract public function sumOfSquaredWeights();

    /**
     * Assigns the query normalization factor to this.
     *
     * @param float $norm
     */
    abstract public function normalize($norm);
}

