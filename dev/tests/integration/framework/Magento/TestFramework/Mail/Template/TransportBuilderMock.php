<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Mail\Template;

class TransportBuilderMock extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var \Magento\Framework\Mail\Message
     */
    protected $_sentMessage;

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->_sentMessage = $this->message;
        parent::reset();
    }

    /**
     * Return message object with prepared data
     *
     * @return \Magento\Framework\Mail\Message|null
     */
    public function getSentMessage()
    {
        return $this->_sentMessage;
    }

    /**
     * Return transport mock.
     *
     * @return \Magento\TestFramework\Mail\TransportInterfaceMock
     */
    public function getTransport()
    {
        $this->prepareMessage();
        $this->reset();
        return new \Magento\TestFramework\Mail\TransportInterfaceMock();
    }
}
