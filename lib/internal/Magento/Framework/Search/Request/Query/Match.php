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
 * Match Query
 */
class Match implements QueryInterface
{
    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Value
     *
     * @var string
     */
    protected $value;

    /**
     * Boost
     *
     * @var int|null
     */
    protected $boost;

    /**
     * Match query array
     * Possible structure:
     * array(
     *     ['field' => 'some_field', 'boost' => 'some_boost'],
     *     ['field' => 'some_field', 'boost' => 'some_boost'],
     * )
     *
     * @var array
     */
    protected $matches = array();

    /**
     * @param string $name
     * @param string $value
     * @param int|null $boost
     * @param array $matches
     */
    public function __construct($name, $value, $boost, array $matches)
    {
        $this->name = $name;
        $this->value = $value;
        $this->boost = $boost;
        $this->matches = $matches;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return QueryInterface::TYPE_MATCH;
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
     * Get Matches
     *
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }
}
