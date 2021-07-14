<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\Media\ConfigInterface;

/**
 * Resolve media path config by resource.
 */
interface MediaConfigResolverInterface
{
    /**
     * Resolve media path config by resource.
     *
     * @param string $resource
     * @return ConfigInterface
     * @throws LocalizedException
     */
    public function execute(string $resource): ConfigInterface;
}
