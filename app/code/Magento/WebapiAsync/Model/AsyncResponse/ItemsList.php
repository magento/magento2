<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model\AsyncResponse;

use Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface;
use Magento\WebapiAsync\Api\Data\ItemStatusInterface;

class ItemsList implements ItemsListInterface
{
    /**
     * @var ItemStatusInterface[]
     */
    private $items;

    /**
     * @var string
     */
    private $groupId;

    /**
     * @var bool
     */
    private $isErrors;

    /**
     * @param $groupId string
     * @param ItemStatusInterface[] $items
     * @param bool $isError
     */
    public function __construct($groupId, array $items = [], $isError = false)
    {
        $this->items = $items;
        $this->groupId = $groupId;
        $this->isErrors = $isError;
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @inheritdoc
     */
    public function setIsErrors($isErrors = false)
    {
        $this->isErrors = $isErrors;
    }

    /**
     * @inheritdoc
     */
    public function getIsErrors()
    {
        return $this->isErrors;
    }
}
