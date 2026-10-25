<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
