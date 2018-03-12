<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * @api
 */
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
