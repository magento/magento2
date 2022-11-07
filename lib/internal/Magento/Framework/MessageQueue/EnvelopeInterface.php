<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * @api
 * @since 103.0.0
 * @since 100.0.2
 */
interface EnvelopeInterface
{
    /**
     * Binary representation of message
     *
     * @return string
     * @since 103.0.0
     */
    public function getBody();

    /**
     * Message metadata
     *
     * @return array
     * @since 103.0.0
     */
    public function getProperties();
}
