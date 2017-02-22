<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Serialization Exception
 */
class SerializationException extends LocalizedException
{
    const DEFAULT_MESSAGE = 'Invalid type';
    const TYPE_MISMATCH = 'Invalid type for value: "%value". Expected Type: "%type".';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null)
    {
        if ($phrase === null) {
            $phrase = new Phrase(self::DEFAULT_MESSAGE);
        }
        parent::__construct($phrase, $cause);
    }
}
