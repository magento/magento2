<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

/**
 * Can try and interpret a given message or fall back to the message text if not possible
 */
class InterpretationMediator implements InterpretationStrategyInterface
{
    /**
     * @var InterpretationStrategy
     */
    private $interpretationStrategy;

    /**
     * @param InterpretationStrategy $interpretationStrategy
     */
    public function __construct(
        InterpretationStrategy $interpretationStrategy
    ) {
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function interpret(MessageInterface $message)
    {
        if ($message->getIdentifier()) {
            try {
                return $this->interpretationStrategy->interpret($message);
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\LogicException $e) {
            }
        }

        return $message->getText();
    }
}
