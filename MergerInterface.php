<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface \Magento\Framework\MessageQueue\MergerInterface
 *
 * @since 2.0.0
 */
interface MergerInterface
{
    /**
     * @param object[] $messages
     * @return object[]|array
     * @since 2.0.0
     */
    public function merge(array $messages);
}
