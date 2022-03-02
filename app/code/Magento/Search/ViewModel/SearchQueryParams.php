<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for search query params
 */
class SearchQueryParams implements ArgumentInterface
{
    /**
     * Return search query params
     *
     * @return array
     */
    public function getSearchQueryParams(): array
    {
        return [];
    }
}
