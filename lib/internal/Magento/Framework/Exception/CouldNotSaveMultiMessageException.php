<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

class CouldNotSaveMultiMessageException extends CouldNotSaveException
{
    /**
     * @param string $mainMessage
     * @param \Magento\Framework\Message\Error[] $errors
     * @param string $separator
     */
    public function __construct($mainMessage, $errors, $separator = '; ')
    {
        $renderedErrors = $this->renderErrors($errors, $separator);
        // $mainMessage should contain %1 to be substituted by concatenated errors
        $phrase = new \Magento\Framework\Phrase($mainMessage, [$renderedErrors]);
        parent::__construct($phrase);
    }

    /**
     * @param \Magento\Framework\Message\Error[] $errors
     * @param string $separator
     * @return string
     */
    private function renderErrors($errors, $separator)
    {
        $renderedErrors = '';
        $eol = '';
        /** @var \Magento\Framework\Message\Error $error */
        foreach ($errors as $error) {
            $renderedErrors .= $eol . __($error->getText())->render();
            $eol = $separator;
        }
        return $renderedErrors;
    }

}
