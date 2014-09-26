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
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ConditionManager
{
    const CONDITION_PATTERN_SIMPLE = '%s %s %s';
    const CONDITION_PATTERN_ARRAY = '%s %s (%s)';
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->adapter = $resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * @param string $query
     * @return string
     */
    public function wrapBrackets($query)
    {
        return empty($query)
            ? $query
            : '(' . $query . ')';
    }

    /**
     * @param string[] $queries
     * @param string $unionOperator
     * @return string
     */
    public function combineQueries(array $queries, $unionOperator)
    {
        return implode(
            ' ' . $unionOperator . ' ',
            array_filter($queries, 'strlen')
        );
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return string
     */
    public function generateCondition($field, $operator, $value)
    {
        return sprintf(
            is_array($value) ? self::CONDITION_PATTERN_ARRAY :self::CONDITION_PATTERN_SIMPLE,
            $this->adapter->quoteIdentifier($field),
            $operator,
            $this->adapter->quote($value)
        );
    }
}
