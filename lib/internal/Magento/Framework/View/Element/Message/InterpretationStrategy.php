<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\RenderersPool;

class InterpretationStrategy implements InterpretationStrategyInterface
{
    /**
     * @var RenderersPool
     */
    private $renderersPool;

    /**
     * @var MessageConfigurationsPool
     */
    private $messageConfigurationsPool;

    /**
     * @param RenderersPool $renderersPool
     * @param MessageConfigurationsPool $messageConfigurationsPool
     */
    public function __construct(
        RenderersPool $renderersPool,
        MessageConfigurationsPool $messageConfigurationsPool
    ) {
        $this->renderersPool = $renderersPool;
        $this->messageConfigurationsPool = $messageConfigurationsPool;
    }

    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     * @throws \LogicException
     */
    public function interpret(MessageInterface $message)
    {
        $messageConfiguration = $this->messageConfigurationsPool->getMessageConfiguration(
            $message->getIdentifier()
        );
        if (null === $messageConfiguration) {
            throw new \LogicException();
        }

        $renderer = $this->renderersPool->get($messageConfiguration['renderer']);
        if (null === $renderer) {
            throw new \LogicException();
        }

        return $renderer->render($message, $messageConfiguration['data']);
    }
}
