<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Condition;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

interface ConditionInterface
{
    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    public function applyToCollection($collection);

    /**
     * @param AdapterInterface $dbAdapter
     * @return Select|string
     */
    public function getIdsSelect($dbAdapter);
}
