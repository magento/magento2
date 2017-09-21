<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\AbstractMessage;

class TestingMessage extends AbstractMessage
{
    const TYPE_TESTING = 'testing';

    /**
     * Getter message type
     *
     * @return string
     */
    public function getType()
    {
        return static::TYPE_TESTING;
    }
}
