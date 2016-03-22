<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail;

class TransportInterfaceMock implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * Mock of send a mail using transport
     *
     * @return void
     */
    public function sendMessage()
    {
        return;
    }
}
