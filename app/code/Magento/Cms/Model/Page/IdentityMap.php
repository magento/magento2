<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\Page;

/**
 * Identity map of loaded pages.
 */
class IdentityMap
{
    /**
     * @var Page[]
     */
    private $pages = [];

    /**
     * Add a page to the list.
     *
     * @param Page $page
     * @throws \InvalidArgumentException When page doesn't have an ID.
     * @return void
     */
    public function add(Page $page): void
    {
        if (!$page->getId()) {
            throw new \InvalidArgumentException('Cannot add non-persisted page to identity map');
        }
        $this->pages[$page->getId()] = $page;
    }

    /**
     * Find a loaded page by ID.
     *
     * @param int $id
     * @return Page|null
     */
    public function get(int $id): ?Page
    {
        if (array_key_exists($id, $this->pages)) {
            return $this->pages[$id];
        }

        return null;
    }

    /**
     * Remove the page from the list.
     *
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        unset($this->pages[$id]);
    }

    /**
     * Clear the list.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->pages = [];
    }
}
