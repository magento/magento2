<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the current page reset plugin is scoped to the storefront and admin areas only.
 *
 * @see \Magento\Theme\Plugin\Data\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * In the global area (CLI, cron, indexers) the current page is not reset past the last page.
     *
     * @return void
     */
    #[AppArea('global')]
    public function testCurrentPageIsNotResetInGlobalArea(): void
    {
        $collection = $this->createCollection();
        $collection->setPageSize(5)->setCurPage(3);

        $this->assertEquals(3, $collection->getCurPage());
    }

    /**
     * In the frontend area a current page past the last page is reset to the first page.
     *
     * @return void
     */
    #[AppArea('frontend')]
    public function testCurrentPageIsResetInFrontendArea(): void
    {
        $collection = $this->createCollection();
        $collection->setPageSize(5)->setCurPage(3);

        $this->assertEquals(1, $collection->getCurPage());
    }

    /**
     * In the admin area a current page past the last page is reset to the first page.
     *
     * @return void
     */
    #[AppArea('adminhtml')]
    public function testCurrentPageIsResetInAdminArea(): void
    {
        $collection = $this->createCollection();
        $collection->setPageSize(5)->setCurPage(3);

        $this->assertEquals(1, $collection->getCurPage());
    }

    /**
     * Create an in-memory collection with enough items to have more than one page.
     *
     * @return Collection
     */
    private function createCollection(): Collection
    {
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        for ($i = 0; $i < 10; $i++) {
            $collection->addItem(new DataObject(['id' => $i]));
        }

        return $collection;
    }
}
