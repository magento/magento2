<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
