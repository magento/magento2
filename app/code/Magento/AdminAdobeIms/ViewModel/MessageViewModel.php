<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class MessageViewModel implements ArgumentInterface
{
    /** @var InterpretationStrategyInterface */
    private InterpretationStrategyInterface $interpretationStrategy;

    /**
     * @param InterpretationStrategyInterface $interpretationStrategy
     */
    public function __construct(
        InterpretationStrategyInterface $interpretationStrategy
    ) {
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * We are using this as the core block automatically wraps the error messages.
     *
     * @see \Magento\Framework\View\Element\Messages::_renderMessagesByType
     * @param array $messages
     * @return string
     */
    public function getMessagesHtml(array $messages): string
    {
        $html = '';
        foreach ($messages as $message) {
            $html .= $this->interpretationStrategy->interpret($message);
        }
        return $html;
    }
}
