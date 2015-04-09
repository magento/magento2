<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\PrivateData\Section;

use Magento\Customer\Model\PrivateData\Section\SectionSourceInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;

/**
 * Messages section
 */
class Messages implements SectionSourceInterface
{
    /** @var MessageManager */
    private $messageManager;

    /**
     * @param MessageManager $messageManager
     */
    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $messages = $this->messageManager->getMessages(true);
        return [
            'messages' => array_reduce($messages->getItems(),
                function(array $result, MessageInterface $message){
                    $result[] = ['type' => $message->getType(), 'text' => $message->getText()];
                    return $result;
                },
                []
            )
        ];
    }
}
