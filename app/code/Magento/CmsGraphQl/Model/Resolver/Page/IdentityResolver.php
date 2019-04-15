<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\GraphQl\Query\IdentityResolverInterface;

/**
 * Identity for resolved CMS page
 */
class IdentityResolver implements IdentityResolverInterface
{
    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData): array
    {
        return empty($resolvedData[PageInterface::PAGE_ID]) ? [] : [$resolvedData[PageInterface::PAGE_ID]];
    }
}
