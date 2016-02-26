<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Get message unique id
     *
     * @return string
     */
    public function getMessageId();

    /**
     * Message metadata
     *
     * @return array
     */
    public function getProperties();
}
