<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface for merging decoded queue messages.
 * @api
 */
interface MergerInterface
{
    /**
     * Merges or/and converts decoded queue messages.
     *
     * MergedMessage object contains array with ids of original queue messages
     *
     * @param object[] $messages
     * @return object[]|MergedMessageInterface[]
     */
    public function merge(array $messages);
}
