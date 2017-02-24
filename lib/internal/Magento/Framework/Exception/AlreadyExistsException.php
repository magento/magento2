<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
