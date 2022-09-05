<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\View\Element;

interface BookmarkContextInterface
{
    /**
     * Retrieve filter data from request or bookmark
     *
     * @return array
     */
    public function getFilterData(): array;

    /**
     * Is bookmark available for ui component
     *
     * @return bool
     */
    public function isBookmarkAvailable(): bool;
}
