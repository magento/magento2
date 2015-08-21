<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param RenderersPool $renderersPool
     */
    public function __construct(
        RenderersPool $renderersPool
    ) {
        $this->renderersPool = $renderersPool;
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
        $renderer = $this->renderersPool->get($message->getIdentifier());
        if (null === $renderer) {
            throw new \LogicException();
        }

        return $renderer->render($message);
    }
}
