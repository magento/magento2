<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * List of bulk operations.
 */
class OperationList implements \Magento\AsynchronousOperations\Api\Data\OperationListInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @param array $items [optional]
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
