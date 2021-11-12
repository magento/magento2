<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\CustomerData;

use Magento\Framework\Message\ManagerInterface as MessageManager;

class MessageService implements MessageServiceInterface
{
    /**
     * Manager messages
     *
     * @var MessageManager
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param MessageManager $messageManager
     */
    public function __construct(
        MessageManager $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messageManager->getMessages(true);
    }
}
