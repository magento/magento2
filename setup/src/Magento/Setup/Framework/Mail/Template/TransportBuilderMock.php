<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Framework\Mail\Template;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Setup\Framework\Mail\TransportInterfaceMock;

/**
 * Mock for mail template transport builder.
 */
class TransportBuilderMock extends TransportBuilder
{
    /**
     * @inheritDoc
     */
    public function getTransport()
    {
        $this->prepareMessage();
        $this->reset();

        return new TransportInterfaceMock($this->message);
    }
}
