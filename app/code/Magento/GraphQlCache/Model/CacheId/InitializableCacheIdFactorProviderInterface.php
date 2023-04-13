<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCache\Model\CacheId;

use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Initializable id factor provider interface for resolver cache.
 */
interface InitializableCacheIdFactorProviderInterface extends CacheIdFactorProviderInterface, InitializableInterface
{
}
