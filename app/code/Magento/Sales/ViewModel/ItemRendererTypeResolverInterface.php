<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel;

/**
 * Item renderer type resolver
 */
interface ItemRendererTypeResolverInterface
{
    /**
     * Get renderer type for provided item object
     *
     * @param \Magento\Framework\DataObject $item
     * @return string|null
     */
    public function resolve(\Magento\Framework\DataObject $item): ?string;
}
