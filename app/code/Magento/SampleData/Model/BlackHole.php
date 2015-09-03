<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

/**
 *  Mail Transport that do not send mail
 */
class BlackHole implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * Send a mail using this transport
     *
     * @return void
     */
    public function sendMessage()
    {
        return true;
    }
}
