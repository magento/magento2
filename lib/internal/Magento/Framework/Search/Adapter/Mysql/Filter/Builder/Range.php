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
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Range implements FilterInterface
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @return \Magento\Framework\DB\Select
     */
    public function buildFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter
    ) {
        $adapter = $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
        /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
        return $this->generateCondition($filter->getField(), $filter->getFrom(), $filter->getTo(), $adapter);
    }

    private function generateCondition($field, $from, $to, AdapterInterface $adapter)
    {
        $hasFromValue = !is_null($from);
        $hasToValue = !is_null($to);

        $condition = '';

        if ($hasFromValue and $hasToValue) {
            $condition = sprintf(
                '%s >= %s %s %s < %s',
                $field,
                $adapter->quote($from),
                \Zend_Db_Select::SQL_AND,
                $field,
                $adapter->quote($to)
            );
        } elseif ($hasFromValue and !$hasToValue) {
            $condition = sprintf(
                '%s >= %s',
                $field,
                $adapter->quote($from)
            );
        } elseif (!$hasFromValue and $hasToValue) {
            $condition = sprintf(
                '%s < %s',
                $field,
                $adapter->quote($to)
            );
        }
        return $condition;
    }
}
