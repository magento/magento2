<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelectedInterface;
use Magento\Framework\View\Result\Page as View;

/**
 * Manage custom layout files for CMS pages.
 *
 * @api
 */
interface CustomLayoutManagerInterface
{
    /**
     * List of available custom files for the given page.
     *
     * @param PageInterface $page
     * @return string[]
     */
    public function fetchAvailableFiles(PageInterface $page): array;

    /**
     * Apply the page's layout settings.
     *
     * @param View $layout
     * @param CustomLayoutSelectedInterface $layoutSelected
     * @return void
     */
    public function applyUpdate(View $layout, CustomLayoutSelectedInterface $layoutSelected): void;
}
