<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

interface MergerInterface
{
    /**
     * @param object[] $messages
     * @return object[]
     */
    public function merge(array $messages);
}
