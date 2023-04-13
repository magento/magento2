<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\CacheId;

/**
 * Initializable id factor provider interface for resolver cache.
 */
interface InitializableCacheIdFactorProviderInterface extends CacheIdFactorProviderInterface, InitializableInterface
{
}
