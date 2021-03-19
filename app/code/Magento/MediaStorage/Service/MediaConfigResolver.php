<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Api\MediaConfigResolverInterface;
use Magento\MediaStorage\Model\Media\ConfigInterface;

/**
 * Resolve media path config by resource.
 */
class MediaConfigResolver implements MediaConfigResolverInterface
{
    /**
     * @var ConfigInterface[]
     */
    private $resolvers;

    /**
     * @param ConfigInterface[] $resolvers
     */
    public function __construct(
        array $resolvers = []
    ) {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $resource): ConfigInterface
    {
        if (isset($this->resolvers[$resource]) && $this->resolvers[$resource] instanceof ConfigInterface) {
            return $this->resolvers[$resource];
        }

        throw new LocalizedException(
            __('Media resource \'%1\' not configured or configured incorrectly.', $resource)
        );
    }
}
