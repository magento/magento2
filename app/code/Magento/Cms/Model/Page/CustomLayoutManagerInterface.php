<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\Page\CustomLayout\Data\CustomLayoutSelectedInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Manage custom layout files for CMS pages.
 */
interface CustomLayoutManagerInterface
{
    /**
     * Save layout file to be used when rendering given page.
     *
     * @throws LocalizedException When failed to save new value.
     * @throws \InvalidArgumentException When invalid file was selected.
     * @throws NoSuchEntityException When given page is not found.
     * @param CustomLayoutSelectedInterface $layout
     * @return void
     */
    public function save(CustomLayoutSelectedInterface $layout): void;

    /**
     * Do not use custom layout update when rendering the page.
     *
     * @throws NoSuchEntityException When given page is not found.
     * @throws LocalizedException When failed to remove existing value.
     * @param int $pageId
     * @return void
     */
    public function deleteFor(int $pageId): void;

    /**
     * List of available custom files for the given page.
     *
     * @throws NoSuchEntityException When given page is not found.
     * @param int $pageId
     * @return string[]
     */
    public function fetchAvailableFiles(int $pageId): array;

    /**
     * Get handles according to the page's settings.
     *
     * @throws NoSuchEntityException When given page is not found.
     * @param int $pageId
     * @return array|null With keys as handle IDs and values as handles.
     */
    public function fetchHandle(int $pageId): ?array;
}
