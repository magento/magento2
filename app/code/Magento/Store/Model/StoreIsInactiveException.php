<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class StoreIsInactiveException extends LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Store is inactive');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
