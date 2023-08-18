<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\StateException;

/**
 * Thrown when the cart is locked for processing.
 */
class CartLockedException extends StateException
{

}
