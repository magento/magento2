<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Class FieldDataConversionException
 * @since 2.2.0
 */
class FieldDataConversionException extends \Exception
{
    /**
     * Message pattern for corrupted data exception
     */
    const MESSAGE_PATTERN = "Error converting field `%s` in table `%s` where `%s`=%s using %s."
                            . PHP_EOL
                            . "Fix data or replace with a valid value."
                            . PHP_EOL
                            . "Failure reason: '%s'";
}
