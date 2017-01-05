<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

interface EnvelopeInterface
{
    /**
     * Binary representation of message
     *
     * @return string
     */
    public function getBody();

    /**
     * Message metadata
     *
     * @return array
     */
    public function getProperties();
}
