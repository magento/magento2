<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Bool Query
 */
class Bool implements QueryInterface
{
    const QUERY_CONDITION_MUST = 'must';
    const QUERY_CONDITION_SHOULD = 'should';
    const QUERY_CONDITION_NOT = 'not';

    /**
     * Boost
     *
     * @var int|null
     */
    protected $boost;

    /**
     * Query Name
     *
     * @var string
     */
    protected $name;

    /**
     * Query names to which result set SHOULD satisfy
     *
     * @var array
     */
    protected $should = array();

    /**
     * Query names to which result set MUST satisfy
     *
     * @var array
     */
    protected $must = array();

    /**
     * Query names to which result set MUST NOT satisfy
     *
     * @var array
     */
    protected $mustNot = array();

    /**
     * @param string $name
     * @param int|null $boost
     * @param array $must
     * @param array $should
     * @param array $not
     */
    public function __construct($name, $boost, array $must = [], array $should = [], array $not = [])
    {
        $this->name = $name;
        $this->boost = $boost;
        $this->must = $must;
        $this->should = $should;
        $this->mustNot = $not;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return QueryInterface::TYPE_BOOL;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Get Should queries
     *
     * @return QueryInterface[]
     */
    public function getShould()
    {
        return $this->should;
    }

    /**
     * Get Must queries
     *
     * @return QueryInterface[]
     */
    public function getMust()
    {
        return $this->must;
    }

    /**
     * Get Must Not queries
     *
     * @return QueryInterface[]
     */
    public function getMustNot()
    {
        return $this->mustNot;
    }
}
