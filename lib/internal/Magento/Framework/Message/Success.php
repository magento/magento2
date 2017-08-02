<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * Success message model
 * @since 2.0.0
 */
class Success extends AbstractMessage
{
    /**
     * Getter message type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType()
    {
        return MessageInterface::TYPE_SUCCESS;
    }
}
