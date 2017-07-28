<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * List of bulk operations.
 * @since 2.2.0
 */
class OperationList implements \Magento\AsynchronousOperations\Api\Data\OperationListInterface
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $items;

    /**
     * @param array $items [optional]
     * @since 2.2.0
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getItems()
    {
        return $this->items;
    }
}
