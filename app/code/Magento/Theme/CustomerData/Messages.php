<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
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
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param MessageManager $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param RequestInterface $request
     */
    public function __construct(
        MessageManager $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        ?RequestInterface $request = null
    ) {
        $this->messageManager = $messageManager;
        $this->interpretationStrategy = $interpretationStrategy;
        $this->request = $request ?: \Magento\Framework\App\ObjectManager::getInstance()->get(RequestInterface::class);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getSectionData()
    {
        $messages = $this->messageManager->getMessages(true);
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

        $forceNewSectionTimestamp = $this->request->getParam('force_new_section_timestamp')
            ?? $this->request->getParam('force_new_section_timestamp');

        if ('true' === $forceNewSectionTimestamp && empty($messageResponse)) {
            throw new LocalizedException(__('Session messages already cleared.'));
        }

        return [
            'messages' => $messageResponse,
        ];
    }
}
