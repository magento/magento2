<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout\Data;

/**
 * Custom layout update file to be used for the specific CMS page.
 *
 * @api
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
