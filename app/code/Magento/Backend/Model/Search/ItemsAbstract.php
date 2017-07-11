<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Framework\DataObject;

abstract class ItemsAbstract extends DataObject implements ItemsInterface
{
    /**
     * {@inheritdoc}
     */
    public function setStart($start)
    {
        return $this->setData(self::START, (int)$start);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($query)
    {
        return $this->setData(self::QUERY, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimit($limit)
    {
        return $this->setData(self::LIMIT, (int)$limit);
    }

}
