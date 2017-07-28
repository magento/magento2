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
 * @since 2.0.0
 */
class Messages implements SectionSourceInterface
{
    /**
     * Manager messages
     *
     * @var MessageManager
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var InterpretationStrategyInterface
     * @since 2.0.0
     */
    private $interpretationStrategy;

    /**
     * Constructor
     *
     * @param MessageManager $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @since 2.0.0
     */
    public function __construct(
        MessageManager $messageManager,
        InterpretationStrategyInterface $interpretationStrategy
    ) {
        $this->messageManager = $messageManager;
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getSectionData()
    {
        $messages = $this->messageManager->getMessages(true);
        return [
            'messages' => array_reduce(
                $messages->getItems(),
                function (array $result, MessageInterface $message) {
                    $result[] = [
                        'type' => $message->getType(),
                        'text' => $this->interpretationStrategy->interpret($message)
                    ];
                    return $result;
                },
                []
            ),
        ];
    }
}
