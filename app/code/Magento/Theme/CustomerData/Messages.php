<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Messages section
 */
class Messages implements SectionSourceInterface
{
    /**
     * Manager messages
     *
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    /**
     * @var MessageServiceInterface
     */
    private $messageService;

    /**
     * Constructor
     *
     * @param MessageManager $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param MessageServiceInterface $messageService
     */
    public function __construct(
        MessageManager $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        MessageServiceInterface $messageService
    ) {
        $this->messageManager = $messageManager;
        $this->interpretationStrategy = $interpretationStrategy;
        $this->messageService = $messageService;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $messages = $this->messageService->getMessages();
        $messageResponse = array_reduce(
            $messages->getItems(),
            function (array $result, MessageInterface $message) {
                $result[] = [
                    'type' => $message->getType(),
                    'text' => $this->interpretationStrategy->interpret($message)
                ];
                return $result;
            },
            []
        );
        return [
            'messages' => $messageResponse
        ];
    }
}
