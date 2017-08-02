<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

/**
 * Class \Magento\Framework\View\Element\Message\InterpretationMediator
 *
 * @since 2.0.0
 */
class InterpretationMediator implements InterpretationStrategyInterface
{
    /**
     * @var InterpretationStrategy
     * @since 2.0.0
     */
    private $interpretationStrategy;

    /**
     * @param InterpretationStrategy $interpretationStrategy
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function interpret(MessageInterface $message)
    {
        if ($message->getIdentifier()) {
            try {
                return $this->interpretationStrategy->interpret($message);
            } catch (\LogicException $e) {
                // pass
            }
        }

        return $message->getText();
    }
}
