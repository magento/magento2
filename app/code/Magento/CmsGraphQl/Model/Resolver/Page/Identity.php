<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page
 */
class Identity implements IdentityInterface
{
    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData[PageInterface::PAGE_ID]) ? [] : [$resolvedData[PageInterface::PAGE_ID]];
    }
}
