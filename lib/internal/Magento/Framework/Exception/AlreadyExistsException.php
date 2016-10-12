<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Class AlreadyExistsException
 */
class AlreadyExistsException extends LocalizedException
{
    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Unique constraint violation found');
        }
        parent::__construct($phrase, $cause);
    }
}
