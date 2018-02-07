<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Stub;

use Magento\Framework\Mail\TransportInterface;

class TransportInterfaceMock implements TransportInterface
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
