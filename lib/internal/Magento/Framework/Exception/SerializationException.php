<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Serialization Exception
 */
class SerializationException extends LocalizedException
{
    const TYPE_MISMATCH = 'Invalid type for value: "%value". Expected Type: "%type".';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null)
    {
        if (is_null($phrase)) {
            $phrase = new Phrase(self::TYPE_MISMATCH);
        }
        parent::__construct($phrase, $cause);
    }
}
