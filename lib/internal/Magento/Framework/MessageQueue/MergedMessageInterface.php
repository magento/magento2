<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface for mapping between original and merged messages.
 * @api
 */
interface MergedMessageInterface
{
    /**
     * Get merged message instance.
     *
     * @return mixed
     */
    public function getMergedMessage();

    /**
     * Get original messages ids connected with the merged message.
     *
     * @return array
     */
    public function getOriginalMessagesIds();
}
