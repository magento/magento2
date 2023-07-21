<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

/**
 * Dehydrates resolved value into serializable restorable snapshots.
 */
interface DehydratorInterface
{
    /**
     * Dehydrate value into restorable snapshots.
     *
     * @param array $resolvedValue
     * @return void
     */
    public function dehydrate(array &$resolvedValue): void;
}
