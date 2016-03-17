<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

interface MergerInterface
{
    /**
     * @param object[] $messages
     * @return object[]
     */
    public function merge(array $messages);
}
