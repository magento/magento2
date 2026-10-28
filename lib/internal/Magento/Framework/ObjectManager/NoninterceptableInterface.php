<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
