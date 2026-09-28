<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
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
