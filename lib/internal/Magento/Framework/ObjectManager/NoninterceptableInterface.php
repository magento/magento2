<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager;

/**
 * Marker interface, used to identify proxies for which we don't need to generate interceptors
 *
 * @api
 */
interface NoninterceptableInterface
{
}
