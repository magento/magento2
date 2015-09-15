<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

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
