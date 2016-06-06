<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

use Magento\Framework\Phrase;

/**
 * Factory to combine several messages into one
 */
class PhraseFactory
{
    /**
     * Combine submessages delimited by separator and render them with main message
     *
     * @param string $mainMessage
     * @param MessageInterface[] $subMessages
     * @param string $separator
     * @return Phrase
     */
    public function create($mainMessage, $subMessages, $separator = '; ')
    {
        $renderedErrors = '';
        $eol = '';
        /** @var MessageInterface $subMessage */
        foreach ($subMessages as $subMessage) {
            if ($subMessage instanceof MessageInterface) {
                $phrase = new Phrase($subMessage->getText());
            } else {
                $phrase = new Phrase('Cannot render error message!');
            }
            $renderedErrors .= $eol . $phrase->render();
            $eol = $separator;
        }

        //$mainMessage should contain %1 to be substituted by concatenated errors
        return new Phrase($mainMessage, [$renderedErrors]);
    }
}
