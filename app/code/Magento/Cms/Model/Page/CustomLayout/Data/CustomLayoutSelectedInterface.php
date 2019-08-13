<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout\Data;

/**
 * Custom layout update file to be used for the specific CMS page.
 */
interface CustomLayoutSelectedInterface
{
    /**
     * CMS page ID.
     *
     * @return int
     */
    public function getPageId(): int;

    /**
     * Custom layout file ID (layout update handle value).
     *
     * @return string
     */
    public function getLayoutFileId(): string;
}
