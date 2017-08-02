<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 2.0.0
 */
class StoreIsInactiveException extends LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @since 2.0.0
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Store is inactive');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
