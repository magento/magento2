<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data\Argument;

/**
 * Recoverable situation of a missing argument value, presence of which is optional according to the business logic.
 * Possible resolution is to use a default argument value, if there is one.
 */
class MissingOptionalValueException extends \RuntimeException
{
}
