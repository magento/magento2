<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Upsell;

/**
 * Check the correct behavior of up-sell products on the configurable product view page
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class UpsellTest extends RelatedTest
{
    /**
     * @var Upsell
     */
    protected $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = $this->layout->createBlock(Upsell::class);
        $this->linkType = 'upsell';
    }

    /**
     * @inheritdoc
     */
    protected function getLinkedItems(): array
    {
        return $this->block->getItems();
    }
}
