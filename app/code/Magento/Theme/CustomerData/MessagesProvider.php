<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\CustomerData;

use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class MessagesProvider implements MessagesProviderInterface
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
     * Return collection object of messages from session
     *
     * @return Collection
     */
    public function getMessages() : Collection
    {
        return $this->messageManager->getMessages(true);
    }
}
