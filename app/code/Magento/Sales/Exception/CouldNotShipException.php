<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Exception\CouldNotShipExceptionInterface;

/**
 * Class CouldNotShipException
 */
class CouldNotShipException extends LocalizedException implements CouldNotShipExceptionInterface
{
}
