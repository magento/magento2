<?php
/**
 * No such entity service exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefront\Exception;

class NotSupportedException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(\Magento\Framework\Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new \Magento\Framework\Phrase('Service not supported.');
        }

        parent::__construct($phrase, $cause, $code);
    }
}
